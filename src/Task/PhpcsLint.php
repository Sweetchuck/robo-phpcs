<?php

namespace Cheppers\Robo\Phpcs\Task;

use Cheppers\AssetJar\AssetJarAware;
use Cheppers\AssetJar\AssetJarAwareInterface;
use Cheppers\Robo\Phpcs\LintReportWrapper\ReportWrapper;
use Robo\Common\BuilderAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\Result;
use Robo\Task\Filesystem\loadTasks as FsLoadTasks;
use Robo\Task\Filesystem\loadShortcuts as FsShortCuts;
use Robo\TaskAccessor;
use Symfony\Component\Process\Process;

/**
 * Class TaskPhpcsLint.
 *
 * @package Cheppers\Robo\Phpcs\Task
 */
class PhpcsLint extends Phpcs implements
    AssetJarAwareInterface,
    BuilderAwareInterface
{
    use AssetJarAware;
    use BuilderAwareTrait;
    use FsLoadTasks;
    use FsShortCuts;
    use TaskAccessor;

    const EXIT_CODE_OK = 0;

    const EXIT_CODE_WARNING = 1;

    const EXIT_CODE_ERROR = 2;

    const EXIT_CODE_UNKNOWN = 3;

    /**
     * @var string[]
     */
    protected $exitMessages = [
        0 => 'PHP Code Sniffer not found any errors :-)',
        1 => 'PHP Code Sniffer found some warnings :-|',
        2 => 'PHP Code Sniffer found some errors :-(',
    ];

    /**
     * @var string
     */
    protected $failOn = 'error';

    /**
     * @var \Cheppers\LintReport\ReporterInterface[]
     */
    protected $lintReporters = [];

    /**
     * TaskPhpcsLint constructor.
     *
     * @param array|NULL $options
     */
    public function __construct(array $options = null)
    {
        parent::__construct();

        if ($options) {
            $this->setOptions($options);
        }
    }

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
    public function failOn($value)
    {
        $this->failOn = $value;

        return $this;
    }

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

    /**
     * Get the configuration.
     *
     * @return array
     *   The PHPCS configuration.
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);

        foreach ($options as $name => $value) {
            switch ($name) {
                case 'assetJarMapping':
                    $this->setAssetJarMapping($value);
                    break;

                case 'failOn':
                    $this->failOn($value);
                    break;

                case 'colors':
                    $this->colors($value);
                    break;

                case 'lintReporters':
                    $this->setLintReporters($value);
                    break;

                case 'reports':
                    $this->reports($value);
                    break;

                case 'reportWidth':
                    $this->reportWidth($value);
                    break;

                case 'severity':
                    $this->severity($value);
                    break;

                case 'errorSeverity':
                    $this->errorSeverity($value);
                    break;

                case 'warningSeverity':
                    $this->warningSeverity($value);
                    break;

                case 'standard':
                    $this->standard($value);
                    break;

                case 'extensions':
                    $this->extensions($value);
                    break;

                case 'sniffs':
                    $this->sniffs($value);
                    break;

                case 'exclude':
                    $this->exclude($value);
                    break;

                case 'ignored':
                    $this->ignore($value);
                    break;

                case 'files':
                    $this->files($value);
                    break;
            }
        }

        return $this;
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
    public function extensions(array $value)
    {
        $this->options['extensions'] = $value;

        return $this;
    }

    /**
     * @param array $value
     *
     * @return $this
     */
    public function sniffs(array $value)
    {
        $this->options['sniffs'] = $value;

        return $this;
    }

    /**
     * @param array $value
     *
     * @return $this
     */
    public function exclude(array $value)
    {
        $this->options['exclude'] = $value;

        return $this;
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
    public function standard($value)
    {
        $this->options['standard'] = $value;

        return $this;
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
    public function colors($value)
    {
        $this->options['colors'] = $value;

        return $this;
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
    public function reports(array $reports)
    {
        foreach ($reports as $report => $fileName) {
            $this->report($report, $fileName);
        }

        return $this;
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
    public function report($report, $fileName = null)
    {
        $this->options['reports'][$report] = $fileName;

        return $this;
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
    public function reportWidth($width)
    {
        $this->options['reportWidth'] = $width;

        return $this;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function severity($value)
    {
        $this->options['severity'] = $value;

        return $this;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function errorSeverity($value)
    {
        $this->options['errorSeverity'] = $value;

        return $this;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function warningSeverity($value)
    {
        $this->options['warningSeverity'] = $value;

        return $this;
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
    public function files(array $files)
    {
        $this->options['files'] = $files;

        return $this;
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
    public function ignore(array $value)
    {
        $this->options['ignored'] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $standard = !empty($this->options['standard']) ? $this->options['standard'] : 'Default';
        $this->printTaskInfo("PHP_CodeSniffer is linting with <info>{$standard}</info> standard");

        $this->options += [
            'reports' => [],
        ];

        $this->options['reports'] = array_diff_key(
            $this->options['reports'],
            array_flip(array_keys($this->options['reports'], false, true))
        );

        $isStdOutputPublic = true;
        if (!array_key_exists('json', $this->options['reports'])) {
            $isStdOutputBusy = array_search(null, $this->options['reports'], true) !== false;
            $this->options['reports']['json'] = $isStdOutputBusy ? tempnam(sys_get_temp_dir(), 'robo-phpcs') : null;
            $isStdOutputPublic = $this->options['reports']['json'] !== null;
        }

        $this->prepareReportDirectories();

        $lintOutput = '';
        $exitMessage = '';
        if ($this->runMode === static::RUN_MODE_CLI) {
            /** @var Process $process */
            $process = new $this->processClass($this->getCommand());
            if ($this->workingDirectory) {
                $process->setWorkingDirectory($this->workingDirectory);
            }

            $this->exitCode = $process->run();
            $lintOutput = $process->getOutput();
            if ($isStdOutputPublic) {
                $this->output()->write($lintOutput);
            }
        } elseif ($this->runMode === static::RUN_MODE_NATIVE) {
            $cwd = getcwd();
            if ($this->workingDirectory) {
                chdir($this->workingDirectory);
            }

            /** @var \PHP_CodeSniffer_CLI $phpcsCli */
            $phpcsCli = new $this->phpCodeSnifferCliClass();

            if ($this->options['reports']['json'] === null) {
                ob_start();
            }

            try {
                $phpcsCli->process($this->getNormalizedOptions($this->options));
            } catch (\Exception $e) {
                $this->exitCode = static::EXIT_CODE_UNKNOWN;
                $exitMessage = $e->getMessage();
            }

            if ($this->options['reports']['json'] === null) {
                $lintOutput = ob_get_contents();
                ob_end_clean();
                if ($isStdOutputPublic) {
                    $this->output()->write($lintOutput);
                }
            }

            if ($this->workingDirectory) {
                chdir($cwd);
            }
        }

        if ($this->isLintSuccess()
            && $this->options['reports']['json'] !== null
            && is_readable($this->options['reports']['json'])
        ) {
            $lintOutput = file_get_contents($this->options['reports']['json']);
        }

        $reportWrapper = null;
        if ($this->exitCode && !$lintOutput) {
            $exitCode = static::EXIT_CODE_UNKNOWN;
            $exitMessage = $exitMessage ?: 'Unknown error';
        } else {
            // @todo Pray for a valid JSON output.
            $report = (array) json_decode($lintOutput, true);
            $report += ['totals' => [], 'files' => []];

            $reportWrapper = new ReportWrapper($report);
            if ($this->isReportHasToBePutBackIntoJar()) {
                $this->setAssetJarValue('report', $reportWrapper);
            }

            foreach ($this->initLintReporters() as $lintReporter) {
                $lintReporter
                    ->setReportWrapper($reportWrapper)
                    ->generate();
            }

            $exitCode = $this->getTaskExitCode($report['totals']);
            $exitMessage = $this->getExitMessage($exitCode);
        }

        return new Result($this, $exitCode, $exitMessage, [
            'report' => $reportWrapper,
        ]);
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function getNormalizedOptions(array $options)
    {
        foreach (array_keys($this->triStateOptions) as $key) {
            if (!isset($options[$key])) {
                unset($options[$key]);
            } else {
                settype($options[$key], 'boolean');
            }
        }

        foreach (array_keys($this->simpleOptions) as $key) {
            if (!isset($options[$key])) {
                unset($options[$key]);
            }
        }

        foreach (array_keys($this->listOptions) as $key) {
            if (!empty($options[$key])) {
                $options[$key] = $this->filterEnabled($options[$key]);
            }
        }

        if (!empty($options['files'])) {
            $options['files'] = $this->filterEnabled($options['files']);
        }

        $options['verbosity'] = 0;

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
     * @return bool
     */
    protected function isReportHasToBePutBackIntoJar()
    {
        return (
            $this->hasAssetJar()
            && $this->getAssetJarMap('report')
            && $this->isLintSuccess()
        );
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
        return in_array($this->exitCode, $this->lintSuccessExitCodes());
    }

    /**
     * Prepare directories for report outputs.
     *
     * @return null|\Robo\Result
     *   Returns NULL on success or an error \Robo\Result.
     */
    protected function prepareReportDirectories()
    {
        if (!isset($this->options['reports'])) {
            return Result::success($this, 'There is no directory to create.');
        }

        foreach (array_filter($this->options['reports']) as $fileName) {
            $dir = pathinfo($fileName, PATHINFO_DIRNAME);
            if (!file_exists($dir)) {
                $result = $this->_mkdir($dir);
                if (!$result->wasSuccessful()) {
                    return $result;
                }
            }
        }

        return Result::success($this, 'All directory was created successfully.');
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
}
