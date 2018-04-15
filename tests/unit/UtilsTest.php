<?php

namespace Sweetchuck\Robo\Phpcs\Tests\Unit;

use Sweetchuck\Robo\Phpcs\Utils;

/**
 * @coversDefaultClass \Sweetchuck\Robo\Phpcs\Utils
 */
class UtilsTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function casesEscapeShellArgWithWildcard(): array
    {
        return [
            'empty' => ["''", ''],
            '* plain' => ["'a'", 'a'],
            '* one' => ["'a'*'b'", 'a*b'],
            '* double' => ["'a'**'b'", 'a**b'],
            '* twice' => ["'a'**'b'*'c'", 'a**b*c'],
            '* lead' => ["*'a'", '*a'],
            '* trail' => ["'a'*", 'a*'],
            '* lead-trail' => ["*'a'*", '*a*'],
            '? one' => ["'a'?'b'", 'a?b'],
            '? double' => ["'a'??'b'", 'a??b'],
            '? twice' => ["'a'??'b'?'c'", 'a??b?c'],
            '? lead' => ["?'a'", '?a'],
            '? trail' => ["'a'?", 'a?'],
            '? lead-trail' => ["?'a'?", '?a?'],
            'mixed' => ["?'a'*'b'?'c'*", '?a*b?c*'],
        ];
    }

    /**
     * @dataProvider casesEscapeShellArgWithWildcard
     *
     * @covers ::escapeShellArgWithWildcard
     */
    public function testEscapeShellArgWithWildcard(string $expected, string $arg): void
    {
        $this->tester->assertEquals($expected, Utils::escapeShellArgWithWildcard($arg));
    }

    public function casesMergeReports(): array
    {
        return [
            'empty' => [
                [
                    'totals' => [
                        'errors' => 0,
                        'warnings' => 0,
                        'fixable' => 0,
                    ],
                    'files' => [],
                ],
                [
                    [],
                ],
            ],
            'simple' => [
                [
                    'totals' => [
                        'errors' => 11,
                        'warnings' => 22,
                        'fixable' => 34,
                    ],
                    'files' => [],
                ],
                [
                    [
                        'totals' => [
                            'errors' => 10,
                            'warnings' => 20,
                            'fixable' => 30,
                        ],
                        'files' => [],
                    ],
                    [
                        'totals' => [
                            'errors' => 1,
                            'warnings' => 2,
                            'fixable' => 4,
                        ],
                        'files' => [],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesMergeReports
     */
    public function testMergeReports(array $expected, array $args): void
    {
        $callable = Utils::class . '::mergeReports';
        $this->tester->assertEquals($expected, call_user_func_array($callable, $args));
    }

    public function casesIsIgnored(): array
    {
        return [
            'empty' => [
                false,
                'a.php',
                '',
            ],
            'directory 1' => [
                true,
                'a/b.php',
                'a/',
            ],
            'directory 2' => [
                true,
                'a/b/c.php',
                'a/b/',
            ],
            'extension 1 true' => [
                true,
                'a.js',
                '*.js',
            ],
            'extension 2 true' => [
                true,
                'a/b/c.js',
                '*.js',
            ],
            'extension 1 false' => [
                false,
                'a.php',
                '*.js',
            ],
            'extension 2 false' => [
                false,
                'a/a.js/c.php',
                '*.js',
            ],
            'recursive extension 1 true' => [
                true,
                'c.js',
                '**/*.js',
            ],
            'recursive extension 2 true' => [
                true,
                'ab/cd.js',
                '**/*.js',
            ],
            'recursive extension 3 true' => [
                true,
                'ab/cd/ef.js',
                '**/*.js',
            ],
            'recursive extension 1 false' => [
                false,
                'a.php',
                '**/*.js',
            ],
            'recursive extension 2 false' => [
                false,
                'a/b.js/c.php',
                '**/*.js',
            ],
            './foo/bar.xml vs foo/' => [
                true,
                './foo/bar.xml',
                'foo/',
            ],
            'foo/bar.xml vs ./foo/' => [
                true,
                'foo/bar.xml',
                './foo/',
            ],
            './foo/bar.xml vs ./foo/' => [
                true,
                './foo/bar.xml',
                './foo/',
            ],
        ];
    }

    /**
     * @dataProvider casesIsIgnored
     */
    public function testIsIgnored(bool $expected, string $fileName, string $pattern): void
    {
        $this->tester->assertEquals($expected, Utils::isIgnored(
            $fileName,
            ($pattern ? [$pattern] : [])
        ));
    }
}
