<?php

namespace Helper\Dummy;

/**
 * Class Process.
 *
 * @package Helper
 */
// @codingStandardsIgnoreStart
class PHP_CodeSniffer_CLI extends \PHP_CodeSniffer_CLI
{
    // @codingStandardsIgnoreEnd

    /**
     * @var int
     */
    public static $numOfErrors = 0;

    public function process($values = [])
    {
        return static::$numOfErrors;
    }
}
