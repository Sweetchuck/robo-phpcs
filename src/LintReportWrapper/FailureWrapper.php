<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Phpcs\LintReportWrapper;

use Sweetchuck\LintReport\FailureWrapperInterface;

class FailureWrapper implements FailureWrapperInterface
{
    protected array $failure = [];

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

    public function severity(): string
    {
        return strtolower($this->failure['type']);
    }

    public function source(): string
    {
        return $this->failure['source'];
    }

    public function line(): int
    {
        return $this->failure['line'];
    }

    public function column(): int
    {
        return $this->failure['column'];
    }

    public function message(): string
    {
        return $this->failure['message'];
    }
}
