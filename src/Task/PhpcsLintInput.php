<?php

namespace Cheppers\Robo\Phpcs\Task;

use Cheppers\Robo\Phpcs\Utils;

/**
 * @package Cheppers\Robo\Phpcs\Task
 */
class PhpcsLintInput extends PhpcsLint
{
    //region Properties
    /**
     * {@inheritdoc}
     */
    protected $addFilesToCliCommand = false;

    /**
     * @var array
     */
    protected $currentFile = [
        'fileName' => '',
        'content' => '',
    ];
    //endregion

    //region Option - stdinPath
    /**
     * @var string|null
     */
    protected $stdinPath = null;

    /**
     * @return mixed|null
     */
    public function getStdinPath()
    {
        return $this->stdinPath;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setStdinPath($value)
    {
        $this->stdinPath = $value;

        return $this;
    }
    //endregion

    public function __construct(array $options = null)
    {
        $this->simpleOptions['stdinPath'] = 'stdin-path';

        parent::__construct($options);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);
        foreach ($options as $name => $value) {
            switch ($name) {
                case 'stdinPath':
                    $this->setStdinPath($value);
                    break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function runLint()
    {
        $reports = [];
        $files = $this->getJarValueOrLocal('files');
        $backupFailOn = $this->getFailOn();

        $this->setFailOn('never');
        foreach ($files as $fileName => $file) {
            if (!is_array($file)) {
                $file = [
                    'fileName' => $fileName,
                    'content' => $file,
                ];
            }

            $this->currentFile = $file;

            $this->setStdinPath($fileName);
            $lintExitCode = $this->lintExitCode;
            parent::runLint();
            $this->lintExitCode = max($lintExitCode, $this->lintExitCode);

            if ($this->report) {
                $reports[] = $this->report;
            }
        }
        $this->setFailOn($backupFailOn);

        $this->report = Utils::mergeReports($reports);
        $this->reportRaw = json_encode($this->report);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommand(array $options = null)
    {
        if ($this->currentFile['content'] === null) {
            // @todo Handle the different working directories.
            $echo = $this->currentFile['command'];
        } else {
            $echo = sprintf('echo -n %s', escapeshellarg($this->currentFile['content']));
        }

        return $echo . ' | ' . parent::getCommand($options);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildOptions()
    {
        return [
            'stdinPath' => $this->currentFile['fileName'] ?: $this->getStdinPath(),
        ] + parent::buildOptions();
    }

    /**
     * @param string $itemName
     *
     * @return mixed|null
     */
    protected function getJarValueOrLocal($itemName)
    {
        $map = $this->getAssetJarMap($itemName);
        if ($map) {
            $value = $this->getAssetJarValue($itemName, $keyExists);
            if ($keyExists) {
                return $value;
            }
        }

        switch ($itemName) {
            case 'files':
                return $this->getFiles();
        }

        return null;
    }

    /**
     * @return string
     */
    protected function getTaskInfoPattern()
    {
        return "{name} is linting <info>{count}</info> files with <info>{standard}</info> standard";
    }

    /**
     * {@inheritdoc}
     */
    protected function getTaskContext($context = null)
    {
        return [
            'count' => count($this->getJarValueOrLocal('files')),
        ] + parent::getTaskContext($context);
    }
}
