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
     * @dataProvider casesEscapeShellArgWithWildcard
     *
     * @covers ::escapeShellArgWithWildcard
     *
     * @param string $expected
     * @param string $arg
     */
    public function testEscapeShellArgWithWildcard($expected, $arg)
    {
        $this->tester->assertEquals($expected, Utils::escapeShellArgWithWildcard($arg));
    }
}
