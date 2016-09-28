<?php

namespace Cheppers\Robo\Phpcs\LintReportWrapper;

use Cheppers\LintReport\FailureWrapperInterface;

/**
 * Class FileWrapper.
 *
 * @package Cheppers\LintReport\Wrapper\Phpcs
 */
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
     * @return string
     */
    public function severity()
    {
        return strtolower($this->failure['type']);
    }

    /**
     * @return string
     */
    public function source()
    {
        return $this->failure['source'];
    }

    /**
     * @return int
     */
    public function line()
    {
        return $this->failure['line'];
    }

    /**
     * @return int
     */
    public function column()
    {
        return $this->failure['column'];
    }

    /**
     * @return string
     */
    public function message()
    {
        return $this->failure['message'];
    }
}
