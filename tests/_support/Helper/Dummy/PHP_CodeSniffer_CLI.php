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

    /**
     * @var string|null
     */
    public static $stdOutput = '';

    public function process($values = [])
    {
        if (static::$stdOutput !== null) {
            echo static::$stdOutput;
        }

        return static::$numOfErrors;
    }
}
