<?php

namespace Helper\Dummy;

/**
 * Class Process.
 *
 * @package Helper
 */
class PHP_CodeSniffer_CLI extends \PHP_CodeSniffer_CLI
{

    /**
     * @var int
     */
    public static $numOfErrors = 0;

    public function process($values = [])
    {
        return static::$numOfErrors;
    }
}
