<?php

namespace Cheppers\Robo\Task\Phpcs;

use Robo\Task\BaseTask;

/**
 * Class TaskPhpcs.
 *
 * @package Cheppers\Robo\Task\Phpcs
 */
abstract class TaskPhpcs extends BaseTask
{

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
    protected $config = [];

    /**
     * TaskPhpcs constructor.
     */
    public function __construct()
    {
        $this->phpcsExecutable = $this->findPhpcs();
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
            if (isset($this->config[$config])) {
                $cmd_pattern .= $this->config[$config] ? " --{$option}" : " --no-{$option}";
            }
        }

        foreach ($this->simpleOptions as $config => $option) {
            if (!empty($this->config[$config])) {
                $cmd_pattern .= " --{$option}=%s";
                $cmd_args[] = escapeshellarg($this->config[$config]);
            }
        }

        foreach ($this->listOptions as $config => $option) {
            if (!empty($this->config[$config])) {
                $items = $this->filterEnabled($this->config[$config]);
                if ($items) {
                    $cmd_pattern .= " --{$option}=%s";
                    $cmd_args[] = escapeshellarg(implode(',', $items));
                }
            }
        }

        if (isset($this->config['reports'])) {
            ksort($this->config['reports']);
            foreach ($this->config['reports'] as $report_type => $report_dst) {
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

        if (!empty($this->config['files'])) {
            $files = $this->filterEnabled($this->config['files']);
            $cmd_pattern .= str_repeat(' %s', count($files));
            foreach ($files as $file) {
                $cmd_args[] = escapeshellarg($file);
            }
        }

        return vsprintf($cmd_pattern, $cmd_args);
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
