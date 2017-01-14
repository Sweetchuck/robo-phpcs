<?php

namespace Cheppers\Robo\Phpcs\LintReportWrapper;

use Cheppers\LintReport\FailureWrapperInterface;

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
    public function severity()
    {
        return strtolower($this->failure['type']);
    }

    /**
     * {@inheritdoc}
     */
    public function source()
    {
        return $this->failure['source'];
    }

    /**
     * {@inheritdoc}
     */
    public function line()
    {
        return $this->failure['line'];
    }

    /**
     * {@inheritdoc}
     */
    public function column()
    {
        return $this->failure['column'];
    }

    /**
     * {@inheritdoc}
     */
    public function message()
    {
        return $this->failure['message'];
    }
}
