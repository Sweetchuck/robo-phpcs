<?php

namespace Sweetchuck\Robo\Phpcs\LintReportWrapper;

use Sweetchuck\LintReport\FailureWrapperInterface;

class FailureWrapper implements FailureWrapperInterface
{
    /**
     * @var array
     */
    protected $failure = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $failure)
    {
        // @todo Validate.
        $this->failure = $failure + [
            'message' => '',
            'source' => '',
            'severity' => 0,
            'type' => '',
            'line' => 0,
            'column' => 0,
            'fixable' => false,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function severity(): string
    {
        return strtolower($this->failure['type']);
    }

    /**
     * {@inheritdoc}
     */
    public function source(): string
    {
        return $this->failure['source'];
    }

    /**
     * {@inheritdoc}
     */
    public function line(): int
    {
        return $this->failure['line'];
    }

    /**
     * {@inheritdoc}
     */
    public function column(): int
    {
        return $this->failure['column'];
    }

    /**
     * {@inheritdoc}
     */
    public function message(): string
    {
        return $this->failure['message'];
    }
}
