<?php

namespace Cheppers\Robo\Phpcs\Task;

use Cheppers\AssetJar\AssetJarAware;
use Cheppers\AssetJar\AssetJarAwareInterface;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\Common\BuilderAwareTrait;
use Robo\Common\IO;
use Robo\Contract\BuilderAwareInterface;
use Robo\Contract\OutputAwareInterface;
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
    ContainerAwareInterface,
    BuilderAwareInterface,
    OutputAwareInterface
{
    use AssetJarAware;
    use BuilderAwareTrait;
    use ContainerAwareTrait;
    use FsLoadTasks;
    use FsShortCuts;
    use TaskAccessor;
    use IO;

    const EXIT_CODE_OK = 0;

    const EXIT_CODE_ERROR = 1;

    /**
     * @var string[]
     */
    protected $exitMessages = [
        0 => 'PHP Code Sniffer not found any errors.',
        1 => 'PHP Code Sniffer found some errors :-(',
    ];

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

                case 'colors':
                    $this->colors($value);
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
            'verbosity' => 1,
        ];
        $this->options['reports'] = array_diff_key(
            $this->options['reports'],
            array_flip(array_keys($this->options['reports'], false, true))
        );

        $this->prepareReportDirectories();

        $lintOutput = '';
        if ($this->runMode === static::RUN_MODE_CLI) {
            /** @var Process $process */
            $process = new $this->processClass($this->getCommand());
            if ($this->workingDirectory) {
                $process->setWorkingDirectory($this->workingDirectory);
            }

            $this->exitCode = $process->run();
            $lintOutput = $process->getOutput();
        } elseif ($this->runMode === static::RUN_MODE_NATIVE) {
            $cwd = getcwd();
            if ($this->workingDirectory) {
                chdir($this->workingDirectory);
            }

            /** @var \PHP_CodeSniffer_CLI $phpcsCli */
            $phpcsCli = new $this->phpCodeSnifferCliClass();

            ob_start();
            $numOfErrors = $phpcsCli->process($this->getNormalizedOptions($this->options));
            $lintOutput = ob_get_contents();
            ob_end_clean();

            $this->exitCode = $numOfErrors ? static::EXIT_CODE_ERROR : static::EXIT_CODE_OK;

            if ($this->workingDirectory) {
                chdir($cwd);
            }
        }

        if ($this->isReportHasToBePutBackIntoJar()) {
            // @todo Pray for a valid JSON output.
            $this->setAssetJarValue('report', json_decode($lintOutput, true));
        } elseif ($lintOutput) {
            $this->output()->writeln($lintOutput);
        }

        return new Result($this, $this->getTaskExitCode(), $this->getExitMessage());
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

        return $options;
    }

    /**
     * @todo Implement.
     *
     * @return int
     */
    public function getTaskExitCode()
    {
        return $this->exitCode;
    }

    /**
     * @return bool
     */
    protected function isReportHasToBePutBackIntoJar()
    {
        return (
            $this->hasAssetJar()
            && $this->getAssetJarMap('report')
            && array_key_exists('reports', $this->options)
            && array_key_exists('json', $this->options['reports'])
            && $this->options['reports']['json'] === null
            && in_array($this->exitCode, $this->lintSuccessExitCodes())
        );
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
     * @return int[]
     */
    protected function lintSuccessExitCodes()
    {
        return [
            static::EXIT_CODE_OK,
            static::EXIT_CODE_ERROR,
        ];
    }

    /**
     * @return string
     */
    protected function getExitMessage()
    {
        if (isset($this->exitMessages[$this->exitCode])) {
            return $this->exitMessages[$this->exitCode];
        }

        return 'Unknown outcome.';
    }
}
