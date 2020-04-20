<?php

namespace Sweetchuck\Robo\Phpcs\Task;

use Psr\Log\NullLogger;
use Robo\Contract\InflectionInterface;
use Robo\TaskInfo;
use Sweetchuck\LintReport\ReportWrapperInterface;
use Sweetchuck\Robo\Phpcs\LintReportWrapper\ReportWrapper;
use Sweetchuck\Robo\Phpcs\Utils;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\Common\IO;
use Robo\Contract\CommandInterface;
use Robo\Contract\OutputAwareInterface;
use Robo\Result;
use Robo\TaskAccessor;
use Robo\Task\BaseTask;
use Robo\Task\Filesystem\loadTasks as FsLoadTasks;
use Robo\Task\Filesystem\loadShortcuts as FsShortCuts;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Webmozart\PathUtil\Path;

/**
 * @todo Add option [--runtime-set key value] ?
 */
abstract class PhpcsLint extends BaseTask implements
    ContainerAwareInterface,
    OutputAwareInterface,
    CommandInterface
{
    use ContainerAwareTrait;
    use FsLoadTasks;
    use FsShortCuts;
    use IO;
    use TaskAccessor;

    const EXIT_CODE_OK = 0;

    const EXIT_CODE_WARNING = 1;

    const EXIT_CODE_ERROR = 2;

    const EXIT_CODE_UNKNOWN = 3;

    protected $taskName = 'PHP_CodeSniffer - lint';

    /**
     * @todo Some kind of dependency injection would be awesome.
     *
     * @var string
     */
    protected $processClass = Process::class;

    /**
     * @var int
     */
    protected $lintExitCode = 0;

    /**
     * @var string
     */
    protected $lintStdOutput = '';

    /**
     * @var bool
     */
    protected $isLintStdOutputPublic = true;

    /**
     * @var string
     */
    protected $reportRaw = '';

    /**
     * @var string[]
     */
    protected $exitMessages = [
        0 => 'PHP Code Sniffer not found any errors :-)',
        1 => 'PHP Code Sniffer found some warnings :-|',
        2 => 'PHP Code Sniffer found some errors :-(',
    ];

    /**
     * @var bool
     */
    protected $addWorkingDirectoryToCliCommand = true;

    /**
     * @var bool
     */
    protected $addFilesToCliCommand = true;

    /**
     * @var array
     */
    protected $report = [];

    /**
     * @var ReportWrapperInterface
     */
    protected $reportWrapper = null;

    protected $triStateOptions = [
        'colors' => 'colors',
    ];

    protected $simpleOptions = [
        'cache' => 'cache',
        'tabWidth' => 'tab-width',
        'reportWidth' => 'report-width',
        'basePath' => 'basepath',
        'severity' => 'severity',
        'errorSeverity' => 'error-severity',
        'warningSeverity' => 'warning-severity',
        'encoding' => 'encoding',
        'parallel' => 'parallel',
    ];

    protected $listOptions = [
        'bootstrap' => 'bootstrap',
        'standards' => 'standard',
        'sniffs' => 'sniffs',
        'exclude' => 'exclude',
        'extensions' => 'extensions',
        'ignored' => 'ignore',
    ];

    protected $flagOptions = [
        'noCache' => 'no-cache',
        'ignoreAnnotations' => 'ignore-annotations',
    ];

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $fs;

    public function __construct(?Filesystem $fs = null)
    {
        $this->fs = $fs ?: new Filesystem();
    }

    //region Properties.

    // region Property - assetNamePrefix.
    /**
     * @var string
     */
    protected $assetNamePrefix = '';

    public function getAssetNamePrefix(): string
    {
        return $this->assetNamePrefix;
    }

    /**
     * @return $this
     */
    public function setAssetNamePrefix(string $value)
    {
        $this->assetNamePrefix = $value;

        return $this;
    }
    // endregion

    //region Property - workingDirectory
    /**
     * @var string
     */
    protected $workingDirectory = '';

    public function getWorkingDirectory(): string
    {
        return $this->workingDirectory;
    }

    /**
     * @return $this
     */
    public function setWorkingDirectory(string $workingDirectory)
    {
        $this->workingDirectory = $workingDirectory;

        return $this;
    }
    //endregion

    //region Property - phpcsExecutable
    /**
     * @var string
     */
    protected $phpcsExecutable = '';

    public function getPhpcsExecutable(): string
    {
        return $this->phpcsExecutable;
    }

    /**
     * @return $this
     */
    public function setPhpcsExecutable(string $phpcsExecutable)
    {
        $this->phpcsExecutable = $phpcsExecutable;

        return $this;
    }
    //endregion

    //region Property - failOn
    /**
     * @var string
     */
    protected $failOn = 'error';

    public function getFailOn(): string
    {
        return $this->failOn;
    }

    /**
     * @return $this
     */
    public function setFailOn(string $value)
    {
        $this->failOn = $value;

        return $this;
    }
    //endregion

    //region Property - lintReporters
    /**
     * @var \Sweetchuck\LintReport\ReporterInterface[]
     */
    protected $lintReporters = [];

    /**
     * @return \Sweetchuck\LintReport\ReporterInterface[]
     */
    public function getLintReporters(): array
    {
        return $this->lintReporters;
    }

    /**
     * @param \Sweetchuck\LintReport\ReporterInterface[] $lintReporters
     *
     * @return $this
     */
    public function setLintReporters(array $lintReporters)
    {
        $this->lintReporters = $lintReporters;

        return $this;
    }

    /**
     * @param string $id
     * @param \Sweetchuck\LintReport\ReporterInterface|null $lintReporter
     *
     * @return $this
     */
    public function addLintReporter(string $id, $lintReporter = null)
    {
        $this->lintReporters[$id] = $lintReporter;

        return $this;
    }

    /**
     * @return $this
     */
    public function removeLintReporter(string $id)
    {
        unset($this->lintReporters[$id]);

        return $this;
    }
    //endregion
    //endregion

    /**
     * @return $this
     */
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            switch ($name) {
                case 'assetNamePrefix':
                    $this->setAssetNamePrefix($value);
                    break;

                case 'workingDirectory':
                    $this->setWorkingDirectory($value);
                    break;

                case 'phpcsExecutable':
                    $this->setPhpcsExecutable($value);
                    break;

                case 'failOn':
                    $this->setFailOn($value);
                    break;

                case 'lintReporters':
                    $this->setLintReporters($value);
                    break;

                case 'colors':
                    $this->setColors($value);
                    break;

                case 'cache':
                    $this->setCache($value);
                    break;

                case 'noCache':
                    $this->setNoCache($value);
                    break;

                case 'tabWidth':
                    $this->setTabWidth($value);
                    break;

                case 'reports':
                    $this->setReports($value);
                    break;

                case 'reportWidth':
                    $this->setReportWidth($value);
                    break;

                case 'basePath':
                    $this->setBasePath($value);
                    break;

                case 'bootstrap':
                    $this->setBootstrap($value);
                    break;

                case 'severity':
                    $this->setSeverity($value);
                    break;

                case 'errorSeverity':
                    $this->setErrorSeverity($value);
                    break;

                case 'warningSeverity':
                    $this->setWarningSeverity($value);
                    break;

                case 'standards':
                    $this->setStandards($value);
                    break;

                case 'sniffs':
                    $this->setSniffs($value);
                    break;

                case 'exclude':
                    $this->setExclude($value);
                    break;

                case 'encoding':
                    $this->setEncoding($value);
                    break;

                case 'parallel':
                    $this->setParallel($value);
                    break;

                case 'extensions':
                    $this->setExtensions($value);
                    break;

                case 'ignore':
                case 'ignored':
                    $this->setIgnore($value);
                    break;

                case 'ignoreAnnotations':
                    $this->setIgnoreAnnotations($value);
                    break;

                case 'files':
                    $this->setFiles($value);
                    break;
            }
        }

        return $this;
    }

    //region Options
    //region Option - colors
    /**
     * @var null|bool
     */
    protected $colors = null;

    public function getColors(): ?bool
    {
        return $this->colors;
    }

    /**
     * Use colors in output.
     *
     * @return $this
     */
    public function setColors(?bool $value)
    {
        $this->colors = $value;

        return $this;
    }
    //endregion

    // region Option - cache
    /**
     * @var string
     */
    protected $cache = '';

    public function getCache(): string
    {
        return $this->cache;
    }

    /**
     * @return $this
     */
    public function setCache(string $cache)
    {
        $this->cache = $cache;

        return $this;
    }
    // endregion

    // region Option - noCache
    /**
     * @var bool
     */
    protected $noCache = false;

    public function getNoCache(): bool
    {
        return $this->noCache;
    }

    /**
     * @return $this
     */
    public function setNoCache(bool $noCache)
    {
        $this->noCache = $noCache;

        return $this;
    }
    // endregion

    // region Option - tabWidth
    /**
     * @var null|int
     */
    protected $tabWidth = null;

    public function getTabWidth(): ?int
    {
        return $this->tabWidth;
    }

    /**
     * @return $this
     */
    public function setTabWidth(?int $tabWidth)
    {
        $this->tabWidth = $tabWidth;

        return $this;
    }
    // endregion

    //region Option - reports
    /**
     * @var array
     */
    protected $reports = [];

    public function getReports(): array
    {
        return $this->reports;
    }

    /**
     * Set reports.
     *
     * @param array $reports
     *   Key-value pairs of report name and file path.
     *
     * @return $this
     *   The called object.
     */
    public function setReports(array $reports)
    {
        foreach ($reports as $report => $fileName) {
            $this->setReport($report, $fileName);
        }

        return $this;
    }

    public function getReport(string $reportName): ?string
    {
        $reports = $this->getReports();

        return array_key_exists($reportName, $reports) ? $reports[$reportName] : null;
    }

    /**
     * Set one report.
     *
     * @param string $report
     *   Name of the report type.
     * @param string $fileName
     *   Write the report to the specified file path.
     *
     * @return $this
     *   The called object.
     */
    public function setReport(string $report, ?string $fileName = null)
    {
        $this->reports[$report] = $fileName;

        return $this;
    }
    //endregion

    //region Option - reportWidth
    /**
     * @var null|int|string
     */
    protected $reportWidth = null;

    /**
     * @return null|int|string
     */
    public function getReportWidth()
    {
        return $this->reportWidth;
    }

    /**
     * Report type.
     *
     * @param null|int|string $width
     *   How many columns wide screen reports should be printed or set to "auto"
     *   to use current screen width, where supported.
     *
     * @return $this
     *   The called object.
     */
    public function setReportWidth($width)
    {
        $this->reportWidth = $width;

        return $this;
    }
    //endregion

    // region Option - basePath
    /**
     * @var string
     */
    protected $basePath = '';

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * @return $this
     */
    public function setBasePath(string $basePath)
    {
        $this->basePath = $basePath;

        return $this;
    }
    // endregion

    // region Option - bootstrap
    /**
     * @var array
     */
    protected $bootstrap = [];

    public function getBootstrap(): array
    {
        return $this->bootstrap;
    }

    /**
     * @return $this
     */
    public function setBootstrap(array $bootstrap)
    {
        $this->bootstrap = $bootstrap;

        return $this;
    }
    // endregion

    //region Option - severity
    /**
     * @var null|int
     */
    protected $severity = null;

    public function getSeverity(): ?int
    {
        return $this->severity;
    }

    /**
     * @return $this
     */
    public function setSeverity(?int $value)
    {
        $this->severity = $value;

        return $this;
    }
    //endregion

    //region Options - errorSeverity
    /**
     * @var int|null
     */
    protected $errorSeverity = null;

    /**
     * @return int|null
     */
    public function getErrorSeverity()
    {
        return $this->errorSeverity;
    }

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setErrorSeverity($value)
    {
        $this->errorSeverity = $value;

        return $this;
    }
    //endregion

    //region Option - warningSeverity
    /**
     * @var null|int
     */
    protected $warningSeverity = null;

    public function getWarningSeverity(): ?int
    {
        return $this->warningSeverity;
    }

    /**
     * @return $this
     */
    public function setWarningSeverity(?int $value)
    {
        $this->warningSeverity = $value;

        return $this;
    }
    //endregion

    //region Option - standard
    /**
     * @var bool[]
     */
    protected $standards = [];

    public function getStandards(): array
    {
        return $this->standards;
    }

    /**
     * Set the name or path of the coding standard to use.
     *
     * @return $this
     */
    public function setStandards(array $standards)
    {
        $this->standards = gettype(reset($standards)) === 'boolean' ?
            $standards
            : array_fill_keys($standards, true);

        return $this;
    }
    //endregion

    //region Option - extensions
    /**
     * @var array
     */
    protected $extensions = [];

    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * List of file extensions to check.
     *
     * Not that extension filtering only valid when checking a directory
     * The type of the file can be specified using: ext/type
     * e.g. module/php.
     *
     * @param string[] $value
     *   File extensions.
     *
     * @return $this
     *
     * @see \PHP_CodeSniffer::setAllowedFileExtensions
     */
    public function setExtensions(array $value)
    {
        $this->extensions = $value;

        return $this;
    }
    //endregion

    //region Option - sniffs
    /**
     * @var array
     */
    protected $sniffs = [];

    public function getSniffs(): array
    {
        return $this->sniffs;
    }

    /**
     * @return $this
     */
    public function setSniffs(array $sniffNames)
    {
        $this->sniffs = $sniffNames;

        return $this;
    }
    //endregion

    //region Option - exclude
    /**
     * @var array
     */
    protected $exclude = [];

    public function getExclude(): array
    {
        return $this->exclude;
    }

    /**
     * @return $this
     */
    public function setExclude(array $value)
    {
        $this->exclude = $value;

        return $this;
    }
    //endregion

    // region Option - encoding
    /**
     * @var string
     */
    protected $encoding = '';

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * @return $this
     */
    public function setEncoding(string $encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }
    // endregion

    // region Option - parallel
    /**
     * @var null|int
     */
    protected $parallel = null;

    public function getParallel(): ?int
    {
        return $this->parallel;
    }

    /**
     * @return $this
     */
    public function setParallel(?int $parallel)
    {
        $this->parallel = $parallel;

        return $this;
    }
    // endregion

    //region Option - ignore
    /**
     * @var string[]
     */
    protected $ignored = [];

    /**
     * @return array|null
     */
    public function getIgnore()
    {
        return $this->ignored;
    }

    /**
     * Set patterns to ignore files.
     *
     * @param string[] $value
     *   File patterns.
     *
     * @return $this
     *   The called object.
     */
    public function setIgnore(array $value)
    {
        $this->ignored = $value;

        return $this;
    }
    //endregion

    // region Option - ignoreAnnotations
    /**
     * @var bool
     */
    protected $ignoreAnnotations = false;

    public function getIgnoreAnnotations(): bool
    {
        return $this->ignoreAnnotations;
    }

    /**
     * @return $this
     */
    public function setIgnoreAnnotations(bool $ignoreAnnotations)
    {
        $this->ignoreAnnotations = $ignoreAnnotations;

        return $this;
    }
    // endregion

    //region Option - files
    /**
     * @var array
     */
    protected $files = [];

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Set the file names to check.
     *
     * @param string[] $files
     *   File names to check.
     *
     * @return $this
     *   The called object.
     */
    public function setFiles(array $files)
    {
        $this->files = $files;

        return $this;
    }
    //endregion
    //endregion

    /**
     * {@inheritdoc}
     */
    public function inflect(InflectionInterface $parent)
    {
        parent::inflect($parent);
        if ($parent instanceof ContainerAwareInterface) {
            $container = $parent->getContainer();
            if ($container) {
                $this->setContainer($container);
            }
        }

        if (!$this->getContainer() && \Robo\Robo::hasContainer()) {
            $this->setContainer(\Robo\Robo::getContainer());
        }

        $container = $this->getContainer();
        if ($container && $container->has('output')) {
            $this->setOutput($container->get('output'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCommand()
    {
        $options = $this->getCommandOptions();

        $cmdPattern = '';
        $cmdArgs = [];

        $wd = $this->getWorkingDirectory();
        if ($this->addWorkingDirectoryToCliCommand && $wd) {
            $cmdPattern .= 'cd %s && ';
            $cmdArgs[] = escapeshellarg($wd);
        }

        $phpcsExecutable = $this->getPhpcsExecutable() ?: $this->findPhpcs();
        $cmdPattern .= '%s';
        $cmdArgs[] = escapeshellcmd($phpcsExecutable);

        foreach ($this->flagOptions as $config => $option) {
            if (!empty($options[$config])) {
                $cmdPattern .= " --{$option}";
            }
        }

        foreach ($this->triStateOptions as $config => $option) {
            if (isset($options[$config])) {
                $cmdPattern .= $options[$config] ? " --{$option}" : " --no-{$option}";
            }
        }

        foreach ($this->simpleOptions as $config => $option) {
            if (isset($options[$config])
                && ($options[$config] === 0 || $options[$config] === '0' || $options[$config])
            ) {
                $cmdPattern .= " --{$option}=%s";
                $cmdArgs[] = escapeshellarg($options[$config]);
            }
        }

        foreach ($this->listOptions as $config => $option) {
            if (!empty($options[$config])) {
                $items = $this->filterEnabled($options[$config]);
                if ($items) {
                    $cmdPattern .= " --{$option}=%s";
                    $cmdArgs[] = escapeshellarg(implode(',', $items));
                }
            }
        }

        ksort($options['reports']);
        foreach ($options['reports'] as $reportType => $reportDst) {
            if ($reportDst === null) {
                $cmdPattern .= ' --report=%s';
                $cmdArgs[] = escapeshellarg($reportType);
            } elseif ($reportDst) {
                $cmdPattern .= ' --report-%s=%s';
                $cmdArgs[] = escapeshellarg($reportType);
                $cmdArgs[] = escapeshellarg($reportDst);
            }
        }

        if ($this->addFilesToCliCommand) {
            $files = $this->filterEnabled($this->getFiles());
            if ($files) {
                $cmdPattern .= ' --' . str_repeat(' %s', count($files));
                foreach ($files as $file) {
                    $cmdArgs[] = Utils::escapeShellArgWithWildcard((string) $file);
                }
            }
        }

        return vsprintf($cmdPattern, $cmdArgs);
    }

    protected function getCommandOptions(): array
    {
        $options = [
            'colors' => $this->getColors(),
            'cache' => $this->getCache(),
            'noCache' => $this->getNoCache(),
            'tabWidth' => $this->getTabWidth(),
            'standards' => $this->getStandards(),
            'reports' => $this->getReports(),
            'reportWidth' => $this->getReportWidth(),
            'basePath' => $this->getBasePath(),
            'bootstrap' => $this->getBootstrap(),
            'severity' => $this->getSeverity(),
            'errorSeverity' => $this->getErrorSeverity(),
            'warningSeverity' => $this->getWarningSeverity(),
            'encoding' => $this->getEncoding(),
            'parallel' => $this->getParallel(),
            'extensions' => $this->getExtensions(),
            'sniffs' => $this->getSniffs(),
            'exclude' => $this->getExclude(),
            'ignored' => $this->getIgnore(),
            'ignoreAnnotations' => $this->getIgnoreAnnotations(),
        ];

        $options['reports'] = array_diff_key(
            $options['reports'],
            array_flip(array_keys($options['reports'], false, true))
        );

        return $options;
    }

    public function getTaskExitCode(array $totals): int
    {
        switch ($this->getFailOn()) {
            case 'never':
                return static::EXIT_CODE_OK;

            case 'warning':
                if (!empty($totals['errors'])) {
                    return static::EXIT_CODE_ERROR;
                }

                return empty($totals['warnings']) ? static::EXIT_CODE_OK : static::EXIT_CODE_WARNING;
        }

        return empty($totals['errors']) ? static::EXIT_CODE_OK : static::EXIT_CODE_ERROR;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $reports = $this->getReports();
        if (!array_key_exists('json', $reports)) {
            $this->isLintStdOutputPublic = array_search(null, $reports, true) !== false;
            $jsonReportDestination = $this->isLintStdOutputPublic ?
                tempnam(sys_get_temp_dir(), 'robo-phpcs')
                : null;
            $this->setReport('json', $jsonReportDestination);
        }

        return $this
            ->runHeader()
            ->runPrepareReportDirectories()
            ->runLint()
            ->runReleaseLintReports()
            ->runReturn();
    }

    /**
     * @return $this
     */
    protected function runHeader()
    {
        $this->printTaskInfo(null, null);

        return $this;
    }

    /**
     * Prepare directories for report outputs.
     *
     * @return $this
     */
    protected function runPrepareReportDirectories()
    {
        $reports = $this->getReports();

        $wd = $this->getWorkingDirectory();
        if ($wd === '.') {
            $wd = '';
        }

        foreach (array_filter($reports) as $fileName) {
            if ($wd !== '' && !Path::isAbsolute($fileName)) {
                $fileName = Path::join($wd, $fileName);
            }

            $dir = pathinfo($fileName, PATHINFO_DIRNAME);
            if (!$this->fs->exists($dir)) {
                $this->fs->mkdir($dir);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function runLint()
    {
        $this->reportRaw = '';
        $this->report = [];
        $this->reportWrapper = null;
        $this->lintExitCode = static::EXIT_CODE_OK;

        /** @var \Symfony\Component\Process\Process $process */
        $process = new $this->processClass($this->getCommand());

        $this->lintExitCode = $process->run();
        $this->lintStdOutput = $process->getOutput();
        $this->reportRaw = $this->lintStdOutput;

        $isLintSuccess = $this->isLintSuccess();

        if (!$isLintSuccess) {
            $logger = $this->logger() ?: new NullLogger();
            $logger->debug($this->lintStdOutput);
        }

        // @todo Relative from workingDirectory.
        $jsonReportDestination = $this->getReport('json');
        if ($isLintSuccess
            && $jsonReportDestination !== null
            && is_readable($jsonReportDestination)
        ) {
            $this->reportRaw = file_get_contents($jsonReportDestination);
        }

        if ($isLintSuccess && $this->reportRaw) {
            // @todo Pray for a valid JSON output.
            $this->report = (array) json_decode($this->reportRaw, true);
            $this->report += ['totals' => [], 'files' => []];
            $this->report['totals'] += ['errors' => 0, 'warnings' => 0, 'fixable' => 0];
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function runReleaseLintReports()
    {
        if (!$this->isLintSuccess() || !$this->reportRaw) {
            return $this;
        }

        if ($this->isLintStdOutputPublic) {
            $this->output()->write($this->lintStdOutput);
        }

        $this->reportWrapper = new ReportWrapper($this->report);
        foreach ($this->initLintReporters() as $lintReporter) {
            $lintReporter
                ->setReportWrapper($this->reportWrapper)
                ->generate();
        }

        return $this;
    }

    protected function runReturn(): Result
    {
        if ($this->isLintSuccess() && $this->report) {
            $exitCode = $this->getTaskExitCode($this->report['totals']);
        } else {
            $exitCode = static::EXIT_CODE_UNKNOWN;
        }

        $assetNamePrefix = $this->getAssetNamePrefix();

        return new Result(
            $this,
            $exitCode,
            $this->getExitMessage($exitCode),
            [
                "{$assetNamePrefix}workingDirectory" => $this->getWorkingDirectory(),
                "{$assetNamePrefix}report" => $this->reportWrapper,
            ]
        );
    }

    protected function findPhpcs(): string
    {
        $suggestions = [
            dirname($_SERVER['argv'][0]) . '/phpcs',
            'vendor/bin/phpcs',
            'bin/phpcs',
        ];

        foreach ($suggestions as $suggestion) {
            if (is_executable($suggestion)) {
                return $suggestion;
            }
        }

        return 'phpcs';
    }

    /**
     * @todo Move to Utils::filterEnabled().
     */
    protected function filterEnabled(array $items): array
    {
        return gettype(reset($items)) === 'boolean' ? array_keys($items, true, true) : $items;
    }

    /**
     * Returns true if the lint ran successfully.
     *
     * Returns true even if there was any code style error or warning.
     */
    protected function isLintSuccess(): bool
    {
        return in_array($this->lintExitCode, $this->lintSuccessExitCodes());
    }

    /**
     * @return \Sweetchuck\LintReport\ReporterInterface[]
     */
    protected function initLintReporters(): array
    {
        $lintReporters = [];
        $container = $this->getContainer();
        foreach ($this->getLintReporters() as $id => $lintReporter) {
            if ($lintReporter === false) {
                continue;
            }

            if (!$lintReporter) {
                $lintReporter = $container->get($id);
            } elseif (is_string($lintReporter)) {
                $lintReporter = $container->get($lintReporter);
            }

            if ($lintReporter instanceof \Sweetchuck\LintReport\ReporterInterface) {
                $lintReporters[$id] = $lintReporter;
                if (!$lintReporter->getDestination()) {
                    $lintReporter->setDestination($this->output());
                }
            }
        }

        return $lintReporters;
    }

    /**
     * @return int[]
     */
    protected function lintSuccessExitCodes(): array
    {
        return [
            static::EXIT_CODE_OK,
            static::EXIT_CODE_WARNING,
            static::EXIT_CODE_ERROR,
        ];
    }

    protected function getExitMessage(int $exitCode): string
    {
        if (isset($this->exitMessages[$exitCode])) {
            return $this->exitMessages[$exitCode];
        }

        return 'Unknown outcome.';
    }

    public function getTaskName(): string
    {
        return $this->taskName ?: TaskInfo::formatTaskName($this);
    }

    /**
     * {@inheritdoc}
     */
    protected function printTaskInfo($text, $context = null)
    {
        parent::printTaskInfo(
            $text ?: $this->getTaskInfoPattern(),
            $context ?: $this->getTaskInfoContext()
        );
    }

    protected function getTaskInfoPattern(): string
    {
        return 'runs <info>{command}</info>';
    }

    protected function getTaskInfoContext(): ?array
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTaskContext($context = null)
    {
        $context = parent::getTaskContext($context);
        $context['name'] = $this->getTaskName();
        $context['command'] = $this->getCommand();
        $context['standard'] = implode(',', $this->filterEnabled($this->getStandards()));

        return $context;
    }
}
