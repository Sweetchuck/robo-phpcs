<?php

namespace Cheppers\Robo\Task\Phpcs;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerInterface;
use Robo\Common\IO;
use Robo\Common\Timer;
use Robo\Config;
use Robo\Result;
use Robo\Task\FileSystem\loadShortcuts as FsShortcuts;
use Symfony\Component\Process\Process;

/**
 * Class TaskPhpcsLint.
 *
 * @package Cheppers\Robo\Task\Phpcs
 */
class TaskPhpcsLint extends TaskPhpcs implements ContainerAwareInterface
{

    use FsShortcuts;
    use Timer;
    use IO;

    /**
     * TaskPhpcsLint constructor.
     *
     * @param array|NULL $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct();
        $this->setConfig($config);
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container)
    {
        return Config::setContainer($container);
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer()
    {
        return Config::getContainer();
    }

    /**
     * Get the configuration.
     *
     * @return array
     *   The PHPCS configuration.
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    public function setConfig(array $config)
    {
        foreach ($config as $name => $value) {
            switch ($name) {
                case 'colors':
                    $this->colors($value);
                    break;

                case 'reports':
                    $this->reports($value);
                    break;

                case 'reportWidth':
                    $this->reportWidth($value);
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
        $this->config['extensions'] = $value;

        return $this;
    }

    /**
     * @param array $value
     *
     * @return $this
     */
    public function sniffs(array $value)
    {
        $this->config['sniffs'] = $value;

        return $this;
    }

    /**
     * @param array $value
     *
     * @return $this
     */
    public function exclude(array $value)
    {
        $this->config['exclude'] = $value;

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
        $this->config['standard'] = $value;

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
        $this->config['colors'] = $value;

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
        foreach ($reports as $report => $file_name) {
            $this->report($report, $file_name);
        }

        return $this;
    }

    /**
     * Set one report.
     *
     * @param string $report
     *   Name of the report type.
     * @param string $file_name
     *   Write the report to the specified file path.
     *
     * @return $this
     *   The called object.
     */
    public function report($report, $file_name = null)
    {
        $this->config['reports'][$report] = $file_name;

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
        $this->config['reportWidth'] = $width;

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
        $this->config['files'] = $files;

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
        $this->config['ignored'] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $standard = !empty($this->config['standard']) ? $this->config['standard'] : 'Default';
        $this->printTaskInfo("PHP_CodeSniffer is linting with <info>{$standard}</info> standard");

        $this->config['reports'] = array_diff_key(
            $this->config['reports'],
            array_flip(array_keys($this->config['reports'], false, true))
        );

        $this->prepareReportDirectories();

        $this->startTimer();
        if ($this->runMode === static::RUN_MODE_CLI) {
            $process = new Process($this->getCommand());
            if ($this->workingDirectory) {
                $process->setWorkingDirectory($this->workingDirectory);
            }

            $this->exitCode = $process->run();
            $this->getOutput()->write($process->getOutput());
        } elseif ($this->runMode === static::RUN_MODE_NATIVE) {
            $cwd = getcwd();
            if ($this->workingDirectory) {
                chdir($this->workingDirectory);
            }
            $phpcs_cli = new \PHP_CodeSniffer_CLI();
            $num_of_errors = $phpcs_cli->process($this->getNormalizedConfig($this->config));
            $this->exitCode = $num_of_errors ? 1 : 0;

            if ($this->workingDirectory) {
                chdir($cwd);
            }
        }
        $this->stopTimer();

        $msg = $this->exitCode ? 'PHP Code Sniffer found some errors :-(' : 'PHP Code Sniffer not found any errors.';

        return new Result($this, $this->exitCode, $msg, ['time' => $this->getExecutionTime()]);
    }

    /**
     * @param array $config
     *
     * @return array
     */
    public function getNormalizedConfig(array $config)
    {
        foreach (array_keys($this->triStateOptions) as $key) {
            if (!isset($config[$key])) {
                unset($config[$key]);
            } else {
                settype($config[$key], 'boolean');
            }
        }

        foreach (array_keys($this->simpleOptions) as $key) {
            if (!isset($config[$key])) {
                unset($config[$key]);
            }
        }

        foreach (array_keys($this->listOptions) as $key) {
            if (!empty($config[$key])) {
                $config[$key] = $this->filterEnabled($config[$key]);
            }
        }

        if (!empty($config['files'])) {
            $config['files'] = $this->filterEnabled($config['files']);
        }

        return $config;
    }

    /**
     * Prepare directories for report outputs.
     *
     * @return null|\Robo\Result
     *   Returns NULL on success or an error \Robo\Result.
     */
    protected function prepareReportDirectories()
    {
        if (!isset($this->config['reports'])) {
            return Result::success($this, 'There is no directory to create.');
        }

        foreach (array_filter($this->config['reports']) as $file_name) {
            $dir = dirname($file_name);
            if (!file_exists($dir)) {
                $result = $this->_mkdir($dir);
                if (!$result->wasSuccessful()) {
                    return $result;
                }
            }
        }

        return Result::success($this, 'All directory was created successfully.');
    }
}
