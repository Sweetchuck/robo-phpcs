<?php

namespace Cheppers\Robo\Phpcs;

/**
 * Class Utils.
 *
 * @package Cheppers\Robo\Phpcs
 */
class Utils
{
    /**
     * Escapes a shell argument which contains a wildcard (* or ?).
     *
     * @param string $arg
     *
     * @return string
     */
    public static function escapeShellArgWithWildcard($arg)
    {
        $parts = preg_split('@([\*\?]+)@', $arg, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $escaped = '';
        foreach ($parts as $part) {
            $isWildcard = (strpos($part, '*') !== false || strpos($part, '?') !== false);
            $escaped .= $isWildcard ? $part : escapeshellarg($part);
        }

        return $escaped ?: "''";
    }
}
