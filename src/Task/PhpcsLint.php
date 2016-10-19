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

/**
 * Class PhpcsLint.
 *
 * @package Cheppers\Robo\Phpcs\Task
 */
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
     * @todo Some kind of dependency injection would be awesome.
     *
     * @var string
     */
    protected $phpCodeSnifferCliClass = \PHP_CodeSniffer_CLI::class;

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
     * @var string
     */
    protected $lintOutput = '';

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
    protected $workingDirectory = null;

    public function getWorkingDirectory()
    {
        return $this->workingDirectory;
    }

    /**
     * @param string $workingDirectory
     *
     * @return $this
     */
    public function setWorkingDirectory($workingDirectory)
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

    /**
     * @return string
     */
    public function getPhpcsExecutable()
    {
        return $this->phpcsExecutable;
    }

    /**
     * @param string $phpcsExecutable
     *
     * @return $this
     */
    public function setPhpcsExecutable($phpcsExecutable)
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

    /**
     * @return string
     */
    public function getFailOn()
    {
        return $this->failOn;
    }

    /**
     * @param string $failOn
     *
     * @return $this
     */
    public function setFailOn($value)
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
    public function getLintReporters()
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
    public function addLintReporter($id, $lintReporter = null)
    {
        $this->lintReporters[$id] = $lintReporter;

        return $this;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function removeLintReporter($id)
    {
        unset($this->lintReporters[$id]);

        return $this;
    }
    //endregion

    /**
     * @param array $options
     *
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
     * @var bool
     */
    protected $colors = null;

    /**
     * @return bool|null
     */
    public function getColors()
    {
        return $this->colors;
    }

    /**
     * Use colors in output.
     *
     * @param bool $value
     *   Use or not to use colors in output.
     *
     * @return $this
     *   The called object.
     */
    public function setColors($value)
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

    public function getReports()
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

    /**
     * @param string $reportName
     *
     * @return string|null
     */
    public function getReport($reportName)
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
    public function setReport($report, $fileName = null)
    {
        $this->reports[$report] = $fileName;

        return $this;
    }
    //endregion

    //region Option - reportWidth
    /**
     * @var int|null
     */
    protected $reportWidth = null;

    /**
     * @return int|string|null
     */
    public function getReportWidth()
    {
        return $this->reportWidth;
    }

    /**
     * Report type.
     *
     * @param int|string $width
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
     * @var int|null
     */
    protected $severity = null;

    /**
     * @return int|null
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setSeverity($value)
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
     * @var int|null
     */
    protected $warningSeverity = null;

    /**
     * @return int|null
     */
    public function getWarningSeverity()
    {
        return $this->warningSeverity;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setWarningSeverity($value)
    {
        $this->warningSeverity = $value;

        return $this;
    }
    //endregion

    //region Option - standard
    /**
     * @var string|null
     */
    protected $standard = null;

    /**
     * @return string|null
     */
    public function getStandard()
    {
        return $this->standard;
    }

    /**
     * Set the name or path of the coding standard to use.
     *
     * @param string $value
     *   The name or path of the coding standard to use.
     *
     * @return $this
     *   The called object.
     */
    public function setStandard($value)
    {
        $this->standard = $value;

        return $this;
    }
    //endregion

    //region Option - extensions
    /**
     * @var array
     */
    protected $extensions = [];

    /**
     * @return array|null
     */
    public function getExtensions()
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
     *   The called object.
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

    /**
     * @return array|null
     */
    public function getSniffs()
    {
        return $this->sniffs;
    }

    /**
     * @param array $value
     *
     * @return $this
     */
    public function setSniffs(array $value)
    {
        $this->sniffs = $value;

        return $this;
    }
    //endregion

    //region Option - exclude
    /**
     * @var array
     */
    protected $exclude = [];

    /**
     * @return array|null
     */
    public function getExclude()
    {
        return $this->exclude;
    }

    /**
     * @param array $value
     *
     * @return $this
     */
    public function setExclude(array $value)
    {
        $this->exclude = $value;

        return $this;
    }
    //endregion

    //region Option - files
    /**
     * @var array
     */
    protected $files = [];

    /**
     * @return array|null
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
    public function __construct(array $options = null)
    {
        $this->setPhpcsExecutable($this->findPhpcs());
        if ($options) {
            $this->setOptions($options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCommand(array $options = null)
    {
        if ($options === null) {
            $options = $this->buildOptions();
        }

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

    /**
     * @return array
     */
    protected function buildOptions()
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
        ];

        $options['reports'] = array_diff_key(
            $options['reports'],
            array_flip(array_keys($options['reports'], false, true))
        );

        return $options;
    }

    /**
     * @param array $totals
     *
     * @return int
     */
    public function getTaskExitCode(array $totals)
    {
        switch ($this->getFailOn()) {
            case 'never':
                return static::EXIT_CODE_OK;

            case 'warning':
                if (!empty($totals['errors'])) {
                    return static::EXIT_CODE_ERROR;
                }

                return empty($totals['warnings']) ? static::EXIT_CODE_OK : static::EXIT_CODE_WARNING;

            case 'error':
                return empty($totals['errors']) ? static::EXIT_CODE_OK : static::EXIT_CODE_ERROR;
        }

        return static::EXIT_CODE_OK;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
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
        $this->isLintStdOutputPublic = true;
        $this->report = [];
        $this->reportWrapper = null;
        $this->lintExitCode = static::EXIT_CODE_OK;

        $options = $this->buildOptions();

        if (!array_key_exists('json', $options['reports'])) {
            $this->isLintStdOutputPublic = array_search(null, $options['reports'], true) !== false;
            $options['reports']['json'] = $this->isLintStdOutputPublic ?
                tempnam(sys_get_temp_dir(), 'robo-phpcs')
                : null;
        }

        /** @var Process $process */
        $process = new $this->processClass($this->getCommand($options));
        if ($this->workingDirectory) {
            $process->setWorkingDirectory($this->workingDirectory);
        }

        $this->lintExitCode = $process->run();
        $this->lintStdOutput = $process->getOutput();
        $this->reportRaw = $this->lintStdOutput;

        if ($this->isLintSuccess()
            && $options['reports']['json'] !== null
            && is_readable($options['reports']['json'])
        ) {
            $this->reportRaw = file_get_contents($options['reports']['json']);
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

    /**
     * @return \Robo\Result
     */
    protected function runReturn()
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

    /**
     * @return string
     */
    protected function findPhpcs()
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
     * @param array $items
     *
     * @return array
     */
    protected function filterEnabled(array $items)
    {
        return gettype(reset($items)) === 'boolean' ? array_keys($items, true, true) : $items;
    }

    /**
     * Returns true if the lint ran successfully.
     *
     * Returns true even if there was any code style error or warning.
     *
     * @return bool
     */
    protected function isLintSuccess()
    {
        return in_array($this->lintExitCode, $this->lintSuccessExitCodes());
    }

    /**
     * @return \Cheppers\LintReport\ReporterInterface[]
     */
    protected function initLintReporters()
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
    protected function lintSuccessExitCodes()
    {
        return [
            static::EXIT_CODE_OK,
            static::EXIT_CODE_WARNING,
            static::EXIT_CODE_ERROR,
        ];
    }

    /**
     * @param int $exitCode
     *
     * @return string
     */
    protected function getExitMessage($exitCode)
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

    /**
     * @return string
     */
    protected function getTaskInfoPattern()
    {
        return "{name} is linting with <info>{standard}</info> standard";
    }

    /**
     * {@inheritdoc}
     */
    protected function getTaskContext($context = null)
    {
        return [
            'standard' => $this->getStandard() ?: 'Default',
        ] + parent::getTaskContext($context);
    }
}
