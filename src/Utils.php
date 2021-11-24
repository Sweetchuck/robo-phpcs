<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Phpcs;

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

    /**
     * @todo \PHP_CodeSniffer::shouldIgnoreFile.
     */
    public static function isIgnored(string $fileName, array $patterns): bool
    {
        if (mb_strpos($fileName, './') === 0) {
            $fileName = mb_substr($fileName, 2);
        }

        foreach ($patterns as $pattern) {
            if (mb_strpos($pattern, './') === 0) {
                $pattern = mb_substr($pattern, 2);
            }

            if (preg_match(static::wildcardToRegexp($pattern), $fileName)) {
                return true;
            }

            if (preg_match('@/$@u', $pattern) && strpos($fileName, $pattern) === 0) {
                return true;
            }

            if (strpos($pattern, '**/') === 0
                && strpos($fileName, '/') === false
                && preg_match(static::wildcardToRegexp($pattern), "a/$fileName")
            ) {
                return true;
            }

            if ($fileName === $pattern) {
                return true;
            }
        }

        return false;
    }

    public static function wildcardToRegexp(string $wildcard): string
    {
        $pattern = '@^' . preg_quote($wildcard, '@') . '$@';

        return str_replace('\*', '.*', $pattern);
    }
}
