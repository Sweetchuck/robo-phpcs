<?php

namespace Sweetchuck\Robo\Phpcs\LintReportWrapper;

use Sweetchuck\LintReport\FileWrapperInterface;
use Sweetchuck\LintReport\ReportWrapperInterface;

class FileWrapper implements FileWrapperInterface
{
    /**
     * @var array
     */
    protected $file = [];

    /**
     * @var array
     */
    public $stats = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $file)
    {
        $this->file = $file + [
            'filePath' => '',
            'errorCount' => '',
            'warningCount' => '',
            'messages' => [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function filePath(): string
    {
        return $this->file['filePath'];
    }

    /**
     * {@inheritdoc}
     */
    public function numOfErrors(): int
    {
        return $this->file['errors'];
    }

    /**
     * {@inheritdoc}
     */
    public function numOfWarnings(): int
    {
        return $this->file['warnings'];
    }

    /**
     * {@inheritdoc}
     */
    public function yieldFailures()
    {
        foreach ($this->file['messages'] as $failure) {
            yield new FailureWrapper($failure);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stats(): array
    {
        if (!$this->stats) {
            $this->stats = [
                'severityWeight' => '',
                'severity' => '',
                'has' => [
                    ReportWrapperInterface::SEVERITY_OK => false,
                    ReportWrapperInterface::SEVERITY_WARNING => false,
                    ReportWrapperInterface::SEVERITY_ERROR => false,
                ],
                'source' => [],
            ];
            foreach ($this->file['messages'] as $failure) {
                $severity = strtolower($failure['type']);
                if ($this->stats['severityWeight'] < $failure['severity']) {
                    $this->stats['severityWeight'] = $failure['severity'];
                    $this->stats['severity'] = $severity;
                }

                $this->stats['has'][$severity] = true;

                $this->stats['source'] += [
                    $failure['source'] => [
                        'severity' => $severity,
                        'count' => 0,
                    ],
                ];
                $this->stats['source'][$failure['source']]['count']++;
            }
        }

        return $this->stats;
    }

    /**
     * {@inheritdoc}
     */
    public function highestSeverity(): string
    {
        if ($this->numOfErrors()) {
            return ReportWrapperInterface::SEVERITY_ERROR;
        }

        if ($this->numOfWarnings()) {
            return ReportWrapperInterface::SEVERITY_WARNING;
        }

        return ReportWrapperInterface::SEVERITY_OK;
    }
}
