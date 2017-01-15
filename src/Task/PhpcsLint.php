<?php

namespace Cheppers\Robo\Phpcs\Task;

use Cheppers\AssetJar\AssetJarAware;
use Cheppers\AssetJar\AssetJarAwareInterface;
use Cheppers\LintReport\ReportWrapperInterface;
use Cheppers\Robo\Phpcs\LintReportWrapper\ReportWrapper;
use Cheppers\Robo\Phpcs\Utils;
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

abstract class PhpcsLint extends BaseTask implements
    AssetJarAwareInterface,
    ContainerAwareInterface,
    OutputAwareInterface,
    CommandInterface
{
    use AssetJarAware;
    use ContainerAwareTrait;
    use FsLoadTasks;
    use FsShortCuts;
    use IO;
    use TaskAccessor;

    const EXIT_CODE_OK = 0;

    const EXIT_CODE_WARNING = 1;

    const EXIT_CODE_ERROR = 2;

    const EXIT_CODE_UNKNOWN = 3;

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
        'standard' => 'standard',
        'reportWidth' => 'report-width',
        'severity' => 'severity',
        'errorSeverity' => 'error-severity',
        'warningSeverity' => 'warning-severity',
    ];

    protected $listOptions = [
        'extensions' => 'extensions',
        'sniffs' => 'sniffs',
        'exclude' => 'exclude',
        'ignored' => 'ignore',
    ];

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
     * @var \Cheppers\LintReport\ReporterInterface[]
     */
    protected $lintReporters = [];

    /**
     * @return \Cheppers\LintReport\ReporterInterface[]
     */
    public function getLintReporters(): array
    {
        return $this->lintReporters;
    }

    /**
     * @param \Cheppers\LintReport\ReporterInterface[] $lintReporters
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
     * @param \Cheppers\LintReport\ReporterInterface|null $lintReporter
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

    /**
     * @return $this
     */
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            switch ($name) {
                case 'assetJar':
                    $this->setAssetJar($value);
                    break;

                case 'assetJarMapping':
                    $this->setAssetJarMapping($value);
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

                case 'reports':
                    $this->setReports($value);
                    break;

                case 'reportWidth':
                    $this->setReportWidth($value);
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

                case 'standard':
                    $this->setStandard($value);
                    break;

                case 'extensions':
                    $this->setExtensions($value);
                    break;

                case 'sniffs':
                    $this->setSniffs($value);
                    break;

                case 'exclude':
                    $this->setExclude($value);
                    break;

                case 'ignore':
                case 'ignored':
                    $this->setIgnore($value);
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
     * @var string
     */
    protected $standard = '';

    public function getStandard(): string
    {
        return $this->standard;
    }

    /**
     * Set the name or path of the coding standard to use.
     *
     * @return $this
     */
    public function setStandard(string $name)
    {
        $this->standard = $name;

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
     * TaskPhpcs constructor.
     */
    public function __construct(array $options = [])
    {
        $this->setPhpcsExecutable($this->findPhpcs());
        if ($options) {
            $this->setOptions($options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCommand()
    {
        $options = $this->getCommandOptions();

        $cmdPattern = '%s';
        $cmdArgs = [
            escapeshellcmd($this->phpcsExecutable),
        ];

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
                    $cmdArgs[] = Utils::escapeShellArgWithWildcard($file);
                }
            }
        }

        return vsprintf($cmdPattern, $cmdArgs);
    }

    protected function getCommandOptions(): array
    {
        $options = [
            'colors' => $this->getColors(),
            'standard' => $this->getStandard(),
            'reports' => $this->getReports(),
            'reportWidth' => $this->getReportWidth(),
            'severity' => $this->getSeverity(),
            'errorSeverity' => $this->getErrorSeverity(),
            'warningSeverity' => $this->getWarningSeverity(),
            'extensions' => $this->getExtensions(),
            'sniffs' => $this->getSniffs(),
            'exclude' => $this->getExclude(),
            'ignored' => $this->getIgnore(),
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
            ->runReleaseAssets()
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
        $fs = new Filesystem();
        foreach (array_filter($reports) as $fileName) {
            $dir = pathinfo($fileName, PATHINFO_DIRNAME);
            if (!file_exists($dir)) {
                $fs->mkdir($dir);
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

        /** @var Process $process */
        $process = new $this->processClass($this->getCommand());
        if ($this->workingDirectory) {
            $process->setWorkingDirectory($this->workingDirectory);
        }

        $this->lintExitCode = $process->run();
        $this->lintStdOutput = $process->getOutput();
        $this->reportRaw = $this->lintStdOutput;

        $jsonReportDestination = $this->getReport('json');
        if ($this->isLintSuccess()
            && $jsonReportDestination !== null
            && is_readable($jsonReportDestination)
        ) {
            $this->reportRaw = file_get_contents($jsonReportDestination);
        }

        if ($this->reportRaw) {
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

    /**
     * @return $this
     */
    protected function runReleaseAssets()
    {
        if ($this->isLintSuccess() && $this->hasAssetJar()) {
            if ($this->getAssetJarMap('report')) {
                $this->setAssetJarValue('report', $this->reportWrapper);
            }
        }

        return $this;
    }

    protected function runReturn(): Result
    {
        if ($this->lintExitCode && !$this->reportRaw) {
            $exitCode = static::EXIT_CODE_UNKNOWN;
        } else {
            $exitCode = $this->getTaskExitCode($this->report['totals']);
        }

        return new Result(
            $this,
            $exitCode,
            $this->getExitMessage($exitCode),
            [
                'workingDirectory' => $this->getWorkingDirectory(),
                'report' => $this->reportWrapper,
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
     * @return \Cheppers\LintReport\ReporterInterface[]
     */
    protected function initLintReporters(): array
    {
        $lintReporters = [];
        $c = $this->getContainer();
        foreach ($this->getLintReporters() as $id => $lintReporter) {
            if ($lintReporter === false) {
                continue;
            }

            if (!$lintReporter) {
                $lintReporter = $c->get($id);
            } elseif (is_string($lintReporter)) {
                $lintReporter = $c->get($lintReporter);
            }

            if ($lintReporter instanceof \Cheppers\LintReport\ReporterInterface) {
                $lintReporters[$id] = $lintReporter;
                if (!$lintReporter->getDestination()) {
                    $lintReporter
                        ->setFilePathStyle('relative')
                        ->setDestination($this->output());
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

    /**
     * {@inheritdoc}
     */
    protected function printTaskInfo($text, $context = null)
    {
        parent::printTaskInfo($text ?: $this->getTaskInfoPattern(), $context);
    }

    protected function getTaskInfoPattern(): string
    {
        return "{name} runs <info>{command}</info>";
    }

    /**
     * {@inheritdoc}
     */
    protected function getTaskContext($context = null)
    {
        return [
            'name' => 'PHP_CodeSniffer',
            'command' => $this->getCommand(),
        ] + parent::getTaskContext($context);
    }
}
