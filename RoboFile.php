<?php

// @codingStandardsIgnoreStart
use Symfony\Component\Process\Process;

/**
 * Class RoboFile.
 */
class RoboFile extends \Robo\Tasks
// @codingStandardsIgnoreEnd
{
    use Cheppers\Robo\Phpcs\Task\LoadTasks;

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
    }

    /**
     * Git "pre-commit" hook callback.
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function githookPreCommit()
    {
        /** @var \Robo\Collection\CollectionBuilder $cb */
        $cb = $this->collectionBuilder();

        return $cb->addTaskList([
            'lint.composer.lock' => $this->taskComposerValidate(),
            'lint.phpcs.psr2' => $this->getTaskPhpcsLint(),
            'codecept' => $this->getTaskCodecept(),
        ]);
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
     * @return \Robo\Collection\CollectionBuilder
     */
    public function lint()
    {
        /** @var \Robo\Collection\CollectionBuilder $cb */
        $cb = $this->collectionBuilder();

        return $cb->addTaskList([
            'lint.composer.lock' => $this->taskComposerValidate(),
            'lint.phpcs.psr2' => $this->getTaskPhpcsLint(),
        ]);
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
        if ($this->isPhpExtensionAvailable('xdebug')) {
            $cmd_pattern = '%s';
            $cmd_args[] = escapeshellcmd("{$this->binDir}/codecept");
        } else {
            $cmd_pattern = '%s -qrr %s';
            $cmd_args[] = escapeshellcmd($this->phpdbgExecutable);
            $cmd_args[] = escapeshellarg("{$this->binDir}/codecept");
        }

        $cmd_pattern .= ' --ansi --verbose --coverage --coverage-xml=%s --coverage-html=%s run';
        $cmd_args[] = escapeshellarg('coverage.xml');
        $cmd_args[] = escapeshellarg('html');

        return $this
          ->taskExec(vsprintf($cmd_pattern, $cmd_args))
          ->printed(false);
    }

    /**
     * @return \Cheppers\Robo\Phpcs\Task\PhpcsLint
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
                'tests/_data/RoboFile.php',
                'tests/_support/Helper/',
                'tests/acceptance/',
                'tests/unit/',
                'RoboFile.php',
            ],
        ]);
    }

    /**
     * @param string $extension
     *
     * @return bool
     */
    protected function isPhpExtensionAvailable($extension)
    {
        $command = sprintf('%s -m', escapeshellcmd($this->phpExecutable));

        $process = new Process($command);
        $exitCode = $process->run();
        if ($exitCode !== 0) {
            throw new \RuntimeException('@todo');
        }

        return in_array($extension, explode("\n", $process->getOutput()));
    }
}
