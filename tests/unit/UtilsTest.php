<?php
use Cheppers\Robo\Phpcs\Utils;

/**
 * Class UtilsTest.
 *
 * @coversDefaultClass \Cheppers\Robo\Phpcs\Utils
 */
// @codingStandardsIgnoreStart
class UtilsTest extends \Codeception\Test\Unit
{
    // @codingStandardsIgnoreEnd

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @return array
     */
    public function casesEscapeShellArgWithWildcard()
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
     * @param string $expected
     * @param string $arg
     *
     * @dataProvider casesEscapeShellArgWithWildcard
     *
     * @covers ::escapeShellArgWithWildcard
     */
    public function testEscapeShellArgWithWildcard($expected, $arg)
    {
        $this->tester->assertEquals($expected, Utils::escapeShellArgWithWildcard($arg));
    }

    public function casesMergeReports()
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
     * @param array $expected
     * @param array $args
     *
     * @dataProvider casesMergeReports
     */
    public function testMergeReports(array $expected, array $args)
    {
        $callable = Utils::class . '::mergeReports';
        $this->tester->assertEquals($expected, call_user_func_array($callable, $args));
    }
}
