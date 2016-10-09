<?php

namespace Cheppers\Robo\Phpcs\Task;

use Cheppers\Robo\Phpcs\Utils;

/**
 * Class PhpcsLintInput.
 *
 * @todo Disallow the "native" run mode.
 *
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
            'stdinPath' => $this->getStdinPath(),
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
            $value = $this->getAssetJarValue($itemName);
            if ($value !== null) {
                return $value;
            }
        }

        switch ($itemName) {
            case 'files':
                return $this->getFiles();
        }

        return null;
    }
}
