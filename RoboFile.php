<?php

// @codingStandardsIgnoreStart
use Cheppers\LintReport\Reporter\CheckstyleReporter;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

/**
 * Class RoboFile.
 */
class RoboFile extends \Robo\Tasks implements ContainerAwareInterface
// @codingStandardsIgnoreEnd
{
    use \Cheppers\Robo\Git\Task\LoadTasks;
    use \Cheppers\Robo\Phpcs\Task\LoadTasks;
    use \League\Container\ContainerAwareTrait;

    /**
     * @var array
     */
    protected $composerInfo = [];

    /**
     * @var array
     */
    protected $codeceptionInfo = [];

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
     * Allowed values: dev, git-hook, ci.
     *
     * @var string
     */
    protected $environment = '';

    /**
     * RoboFile constructor.
     */
    public function __construct()
    {
        putenv('COMPOSER_DISABLE_XDEBUG_WARN=1');
        $this->initComposerInfo();
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        \Cheppers\LintReport\Reporter\BaseReporter::lintReportConfigureContainer($this->container);

        return $this;
    }

    protected function getEnvironment()
    {
        $env = getenv('ROBO_PHPCS_ENVIRONMENT');
        if ($env) {
            return $env;
        }

        return $this->environment ?: 'dev';
    }

    /**
     * Git "pre-commit" hook callback.
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function githookPreCommit()
    {
        $this->environment = 'git-hook';

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
     * @return $this
     */
    protected function initCodeceptionInfo()
    {
        if ($this->codeceptionInfo) {
            return $this;
        }

        if (is_readable('codeception.yml')) {
            $this->codeceptionInfo = Yaml::parse(file_get_contents('codeception.yml'));
        } else {
            $this->codeceptionInfo = [
                'paths' => [
                    'log' => 'tests/_output',
                ],
            ];
        }

        return $this;
    }

    /**
     * @return \Robo\Collection\CollectionBuilder
     */
    protected function getTaskCodecept()
    {
        $this->initCodeceptionInfo();

        $cmd_args = [];
        if ($this->isPhpExtensionAvailable('xdebug')) {
            $cmd_pattern = '%s';
            $cmd_args[] = escapeshellcmd("{$this->binDir}/codecept");
        } else {
            $cmd_pattern = '%s -qrr %s';
            $cmd_args[] = escapeshellcmd($this->phpdbgExecutable);
            $cmd_args[] = escapeshellarg("{$this->binDir}/codecept");
        }

        $cmd_pattern .= ' --ansi';
        $cmd_pattern .= ' --verbose';

        $cmd_pattern .= ' --coverage=%s';
        $cmd_args[] = escapeshellarg('coverage/coverage.serialized');

        $cmd_pattern .= ' --coverage-xml=%s';
        $cmd_args[] = escapeshellarg('coverage/coverage.xml');

        $cmd_pattern .= ' --coverage-html=%s';
        $cmd_args[] = escapeshellarg('coverage/html');

        $cmd_pattern .= ' run';

        $reportsDir = $this->codeceptionInfo['paths']['log'];

        /** @var \Robo\Collection\CollectionBuilder $cb */
        $cb = $this->collectionBuilder();
        $cb->addTaskList([
            'prepareCoverageDir' => $this->taskFilesystemStack()->mkdir("$reportsDir/coverage"),
            'runCodeception' => $this->taskExec(vsprintf($cmd_pattern, $cmd_args)),
        ]);

        return $cb;
    }

    /**
     * @return \Cheppers\Robo\Phpcs\Task\PhpcsLintFiles | \Robo\Collection\CollectionBuilder
     */
    protected function getTaskPhpcsLint()
    {
        $env = $this->getEnvironment();

        $files = [
            'src/',
            'tests/_data/RoboFile.php',
            'tests/_support/Helper/',
            'tests/acceptance/',
            'tests/unit/',
            'RoboFile.php',
        ];

        $options = [
            'standard' => 'PSR2',
            'lintReporters' => [
                'lintVerboseReporter' => null,
            ],
        ];

        if ($env === 'ci') {
            $checkstyleLintReporter = new CheckstyleReporter();
            $checkstyleLintReporter->setDestination('tests/_output/checkstyle/phpcs.psr2.xml');
            $options['lintReporters']['lintCheckstyleReporter'] = $checkstyleLintReporter;
        }

        if ($env === 'ci' || $env === 'dev') {
            return $this->taskPhpcsLintFiles($options + ['files' => $files]);
        }

        /** @var \Robo\Collection\CollectionBuilder $cb */
        $cb = $this->collectionBuilder();

        if ($env === 'git-hook') {
            $assetJar = new Cheppers\AssetJar\AssetJar();

            $cb->addTaskList([
                'git.staged' => $this
                    ->taskGitReadStagedFiles()
                    ->setAssetJar($assetJar)
                    ->setAssetJarMap('files', ['files'])
                    ->setPaths($files),
                'phpcs.psr2' => $this
                    ->taskPhpcsLintInput($options)
                    ->setAssetJar($assetJar)
                    ->setAssetJarMap('files', ['files'])
                    ->setAssetJarMap('report', ['report']),
            ]);
        }

        return $cb;
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
