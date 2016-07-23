<?php

// @codingStandardsIgnoreStart
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
     * RoboFile constructor.
     */
    public function __construct()
    {
        $this->initComposerInfo();

        $this->setContainer(\Robo\Config::getContainer());

        /** @var \Robo\Container\RoboContainer $c */
        $c = $this->getContainer();
        $c
            ->addServiceProvider(static::getPhpcsServiceProvider())
            ->addServiceProvider(\Robo\Task\Filesystem\loadTasks::getFilesystemServices());
    }

    /**
     * Git "pre-commit" hook callback.
     *
     * @return \Robo\Collection\Collection
     */
    public function githookPreCommit()
    {
        return $this
            ->collection()
            ->add($this->taskComposerValidate(), 'lint.composer.lock')
            ->add($this->getTaskPhpcsLint(), 'lint.phpcs.psr2')
            ->add($this->getTaskPhpunit(['colors' => true]), 'phpunit');
    }

    /**
     * Run the Robo unit tests.
     */
    public function test()
    {
        $options = func_get_arg(0);
        $config = [
            'colors' => empty($options['no-ansi']),
        ];

        return $this->getTaskPhpunit($config);
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
     * @param array $config
     *
     * @return \Robo\Task\Base\Exec
     */
    protected function getTaskPhpunit(array $config = [])
    {
        $cmd_pattern = '%s';
        $cmd_args = [
            escapeshellcmd("{$this->binDir}/phpunit"),
        ];

        $cmd_pattern .= ' --colors=%s';
        $cmd_args[] = empty($config['colors']) ? 'never' : 'always';

        return $this->taskExec(vsprintf($cmd_pattern, $cmd_args));
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
                'checkstyle' => 'reports/checkstyle.phpcs-psr2.xml',
            ],
            'files' => [
                'src/',
                'src-dev/',
                'RoboFile.php',
            ],
        ]);
    }
}
