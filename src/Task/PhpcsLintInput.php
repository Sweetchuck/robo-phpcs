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
     * @var null|string
     */
    protected $stdinPath = null;

    public function getStdinPath(): ?string
    {
        return $this->stdinPath;
    }

    /**
     * @return $this
     */
    public function setStdinPath(?string $value)
    {
        $this->stdinPath = $value;

        return $this;
    }
    //endregion

    public function __construct(array $options = [])
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
                // @codingStandardsIgnoreStart
                case 'stdinPath':
                // @codingStandardsIgnoreEnd
                    $this->setStdinPath($value);
                    break;
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function runLint()
    {
        $reports = [];
        $files = $this->getJarValueOrLocal('files');
        $backupFailOn = $this->getFailOn();

        $ignorePatterns = $this->filterEnabled($this->getIgnore());

        $this->setFailOn('never');
        foreach ($files as $fileName => $file) {
            if (!is_array($file)) {
                $file = [
                    'fileName' => $fileName,
                    'content' => $file,
                ];
            }

            if (Utils::isIgnored($file['fileName'], $ignorePatterns)) {
                continue;
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
    protected function getCommandOptions(): array
    {
        return [
            'stdinPath' => $this->currentFile['fileName'] ?: $this->getStdinPath(),
        ] + parent::getCommandOptions();
    }

    /**
     * @return mixed
     */
    protected function getJarValueOrLocal(string $itemName)
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
     * {@inheritdoc}
     */
    protected function getTaskInfoPattern(): string
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
