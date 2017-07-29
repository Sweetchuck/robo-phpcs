<?php

namespace Sweetchuck\Robo\Phpcs\LintReportWrapper;

use Sweetchuck\LintReport\ReportWrapperInterface;

class ReportWrapper implements ReportWrapperInterface
{
    /**
     * @var array
     */
    protected $report = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $report = null)
    {
        if ($report !== null) {
            $this->setReport($report);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getReport(): array
    {
        return $this->report;
    }

    /**
     * {@inheritdoc}
     */
    public function setReport(array $report)
    {
        $this->report = $report;
        $this->report += [
            'totals' => [
                'errors' => 0,
                'warnings' => 0,
                'fixable' => 0,
            ],
            'files' => [],
        ];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function countFiles(): int
    {
        return count($this->report['files']);
    }

    /**
     * {@inheritdoc}
     */
    public function yieldFiles()
    {
        foreach ($this->report['files'] as $filePath => $file) {
            $file['filePath'] = $filePath;
            yield $filePath => new FileWrapper($file);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function numOfErrors(): int
    {
        return $this->report['totals']['errors'];
    }

    /**
     * {@inheritdoc}
     */
    public function numOfWarnings(): int
    {
        return $this->report['totals']['warnings'];
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
