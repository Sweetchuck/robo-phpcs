<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Phpcs\Task;

use Psr\Log\NullLogger;
use Sweetchuck\Robo\Phpcs\Utils;
use Symfony\Component\Filesystem\Filesystem;

class PhpcsLintInput extends PhpcsLint
{

    protected string $taskName = 'PHP_CodeSniffer - lint StdInput';

    //region Properties
    protected bool $addWorkingDirectoryToCliCommand = false;

    protected bool $addFilesToCliCommand = false;

    protected array $currentFile = [
        'fileName' => '',
        'content' => '',
    ];
    //endregion

    //region Option - stdinPath
    protected ?string $stdinPath = null;

    public function getStdinPath(): ?string
    {
        return $this->stdinPath;
    }

    public function setStdinPath(?string $value): static
    {
        $this->stdinPath = $value;

        return $this;
    }
    //endregion

    public function __construct(?Filesystem $fs = null)
    {
        parent::__construct($fs);
        $this->simpleOptions['stdinPath'] = 'stdin-path';
    }

    public function setOptions(array $options): static
    {
        parent::setOptions($options);
        foreach ($options as $name => $value) {
            switch ($name) {
                case 'stdinPath':
                    $this->setStdinPath($value);
                    break;
            }
        }

        return $this;
    }



    protected function runLint(): static
    {
        $logger = $this->logger ?: new NullLogger();
        $reports = [];
        $files = $this->getFiles();
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

            $logContext = [
                'fileName' => $file['fileName'],
            ];

            if (Utils::isIgnored($file['fileName'], $ignorePatterns)) {
                $logger->debug('Skip <info>{fileName}</info>', $logContext);

                continue;
            }

            $logger->debug('Lint <info>{fileName}</info>', $logContext);

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
        $command = '';

        $wd = $this->getWorkingDirectory();
        if ($wd) {
            $command = 'cd ' . escapeshellarg($wd) . ' && ';
        }

        if ($this->currentFile['content'] === null) {
            // @todo Handle the different working directories.
            $command .= $this->currentFile['command'];
        } else {
            $command .= sprintf('echo -n %s', escapeshellarg($this->currentFile['content']));
        }

        return $command . ' | ' . parent::getCommand($options);
    }

    protected function getCommandOptions(): array
    {
        return [
            'stdinPath' => $this->currentFile['fileName'] ?: $this->getStdinPath(),
        ] + parent::getCommandOptions();
    }

    protected function getTaskInfoPattern(): string
    {
        return '';
    }

    protected function getTaskInfoContext(): ?array
    {
        $context = (array) parent::getTaskInfoContext();

        $context['count'] = 42;
        $context['standard'] = 'foo';

        return $context;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTaskContext($context = null)
    {
        $context = parent::getTaskContext($context);
        $context['count'] = count($this->getFiles());

        return $context;
    }
}
