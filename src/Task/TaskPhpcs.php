<?php

namespace Cheppers\Robo\Phpcs\Task;

use Robo\Common\IO;
use Robo\Task\BaseTask;
use Symfony\Component\Process\Process;

/**
 * Class TaskPhpcs.
 *
 * @package Cheppers\Robo\Phpcs\Task
 */
abstract class TaskPhpcs extends BaseTask
{

    use IO;

    const RUN_MODE_CLI = 'cli';

    const RUN_MODE_NATIVE = 'native';

    /**
     * @var int
     */
    protected $exitCode = 0;

    /**
     * @var string
     */
    protected $phpcsExecutable = 'false';

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
     * @var string
     */
    protected $workingDirectory = null;

    /**
     * @var string
     */
    protected $runMode = 'native';

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

    /**
     * Configuration to pass to the PHPCS object.
     *
     * @var array
     */
    protected $options = [];

    /**
     * TaskPhpcs constructor.
     */
    public function __construct()
    {
        $this->phpcsExecutable = $this->findPhpcs();
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            switch ($name) {
                case 'workingDirectory':
                    $this->dir($value);
                    break;

                case 'phpcsExecutable':
                    $this->phpcsExecutable($value);
                    break;

                case 'runMode':
                    $this->runMode($value);
                    break;
            }
        }

        return $this;
    }

    /**
     * @param string $dir
     *
     * @return $this
     */
    public function dir($dir)
    {
        $this->workingDirectory = $dir;

        return $this;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function phpcsExecutable($path)
    {
        $this->phpcsExecutable = $path;

        return $this;
    }

    /**
     * @param string $run_mode
     *
     * @return $this
     */
    public function runMode($run_mode)
    {
        if ($run_mode !== static::RUN_MODE_NATIVE && $run_mode !== static::RUN_MODE_CLI) {
            throw new \InvalidArgumentException("Invalid argument: '$run_mode'");
        }

        $this->runMode = $run_mode;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommand()
    {
        $cmd_pattern = '%s';
        $cmd_args = [
            escapeshellcmd($this->phpcsExecutable),
        ];

        foreach ($this->triStateOptions as $config => $option) {
            if (isset($this->options[$config])) {
                $cmd_pattern .= $this->options[$config] ? " --{$option}" : " --no-{$option}";
            }
        }

        foreach ($this->simpleOptions as $config => $option) {
            if (isset($this->options[$config])
                && ($this->options[$config] === 0 || $this->options[$config] === '0' || $this->options[$config])
            ) {
                $cmd_pattern .= " --{$option}=%s";
                $cmd_args[] = escapeshellarg($this->options[$config]);
            }
        }

        foreach ($this->listOptions as $config => $option) {
            if (!empty($this->options[$config])) {
                $items = $this->filterEnabled($this->options[$config]);
                if ($items) {
                    $cmd_pattern .= " --{$option}=%s";
                    $cmd_args[] = escapeshellarg(implode(',', $items));
                }
            }
        }

        if (isset($this->options['reports'])) {
            ksort($this->options['reports']);
            foreach ($this->options['reports'] as $report_type => $report_dst) {
                if ($report_dst === null) {
                    $cmd_pattern .= ' --report=%s';
                    $cmd_args[] = escapeshellarg($report_type);
                } elseif ($report_dst) {
                    $cmd_pattern .= ' --report-%s=%s';
                    $cmd_args[] = escapeshellarg($report_type);
                    $cmd_args[] = escapeshellarg($report_dst);
                }
            }
        }

        if (!empty($this->options['files'])) {
            $files = $this->filterEnabled($this->options['files']);
            $cmd_pattern .= str_repeat(' %s', count($files));
            foreach ($files as $file) {
                $cmd_args[] = escapeshellarg($file);
            }
        }

        return vsprintf($cmd_pattern, $cmd_args);
    }

    /**
     * @return string
     */
    protected function findPhpcs()
    {
        $suggestions = [
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
}
