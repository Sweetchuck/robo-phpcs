<?php

namespace Cheppers\Robo\Phpcs;

class Utils
{
    /**
     * Escapes a shell argument which contains a wildcard (* or ?).
     */
    public static function escapeShellArgWithWildcard(string $arg): string
    {
        $parts = preg_split('@([\*\?]+)@', $arg, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $escaped = '';
        foreach ($parts as $part) {
            $isWildcard = (strpos($part, '*') !== false || strpos($part, '?') !== false);
            $escaped .= $isWildcard ? $part : escapeshellarg($part);
        }

        return $escaped ?: "''";
    }

    public static function mergeReports(array $reports): array
    {
        if (func_num_args() > 1) {
            $reports = func_get_args();
        }

        $merged = [
            'totals' => [
                'errors' => 0,
                'warnings' => 0,
                'fixable' => 0,
            ],
            'files' => [],
        ];

        foreach ($reports as $report) {
            $merged['totals']['errors'] += $report['totals']['errors'];
            $merged['totals']['warnings'] += $report['totals']['warnings'];
            $merged['totals']['fixable'] += $report['totals']['fixable'];
            // @todo Support the same file in more than one report.
            $merged['files'] += $report['files'];
        }

        return $merged;
    }
}
