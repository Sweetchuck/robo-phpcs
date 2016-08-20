<?php

// @codingStandardsIgnoreStart
use Symfony\Component\Process\Process;

/**
 * Class RoboFile.
 */
class RoboFile extends \Robo\Tasks
// @codingStandardsIgnoreEnd
{
    use Cheppers\Robo\Task\Phpcs\LoadTasks;

    /**
     * @var array
     */
    protected $composerInfo = [];

    /**
     * @var string
     */
    protected $packageVendor = '';

    /**
     * @var string
     */
    protected $packageName = '';

    /**
     * @var string
     */
    protected $binDir = 'vendor/bin';

    /**
     * @var string
     */
    protected $phpExecutable = 'php';

    /**
     * @var string
     */
    protected $phpdbgExecutable = 'phpdbg';

    /**
     * RoboFile constructor.
     */
    public function __construct()
    {
        $this->initComposerInfo();

        $this->setContainer(\Robo\Robo::getContainer());

        /** @var \League\Container\Container $c */
        $c = $this->getContainer();
        $c
            ->addServiceProvider(static::getPhpcsServiceProvider())
            ->addServiceProvider(\Robo\Task\Filesystem\loadTasks::getFilesystemServices());
    }

    /**
     * Git "pre-commit" hook callback.
     *
     * @return \Robo\Collection\CollectionInterface
     */
    public function githookPreCommit()
    {
        return $this
            ->collection()
            ->add($this->taskComposerValidate(), 'lint.composer.lock')
            ->add($this->getTaskPhpcsLint(), 'lint.phpcs.psr2')
            ->add($this->getTaskCodecept(), 'codecept');
    }

    /**
     * Run the Robo unit tests.
     */
    public function test()
    {
        return $this->getTaskCodecept();
    }

    /**
     * Run code style checkers.
     *
     * @return \Robo\Collection\Collection
     */
    public function lint()
    {
        return $this
            ->collection()
            ->add($this->taskComposerValidate(), 'lint.composer.lock')
            ->add($this->getTaskPhpcsLint(), 'lint.phpcs.psr2');
    }

    /**
     * @return $this
     */
    protected function initComposerInfo()
    {
        if ($this->composerInfo || !is_readable('composer.json')) {
            return $this;
        }

        $this->composerInfo = json_decode(file_get_contents('composer.json'), true);
        list($this->packageVendor, $this->packageName) = explode('/', $this->composerInfo['name']);

        if (!empty($this->composerInfo['config']['bin-dir'])) {
            $this->binDir = $this->composerInfo['config']['bin-dir'];
        }

        return $this;
    }

    /**
     * @return \Robo\Task\Base\Exec
     */
    protected function getTaskCodecept()
    {
        $cmd_args = [];
        if ($this->isXdebugAvailable()) {
            $cmd_pattern = '%s';
            $cmd_args[] = escapeshellcmd("{$this->binDir}/codecept");
        } else {
            $cmd_pattern = '%s -qrr %s';
            $cmd_args[] = escapeshellcmd($this->phpdbgExecutable);
            $cmd_args[] = escapeshellarg("{$this->binDir}/codecept");
        }

        $cmd_pattern .= ' --ansi --coverage --coverage-xml --coverage-html=html run';

        return $this
          ->taskExec(vsprintf($cmd_pattern, $cmd_args))
          ->printed(false);
    }

    /**
     * @return \Cheppers\Robo\Task\Phpcs\TaskPhpcsLint
     */
    protected function getTaskPhpcsLint()
    {
        return $this->taskPhpcsLint([
            'colors' => 'always',
            'standard' => 'PSR2',
            'reports' => [
                'full' => null,
                'checkstyle' => 'tests/_output/checkstyle/phpcs-psr2.xml',
            ],
            'files' => [
                'src/',
                'RoboFile.php',
            ],
        ]);
    }

    /**
     * @return bool
     */
    protected function isXdebugAvailable()
    {
        $command = sprintf('%s -m | grep xdebug', escapeshellcmd($this->phpExecutable));

        return (new Process($command))->run() === 0;
    }
}
