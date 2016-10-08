<?php

use Cheppers\Robo\Phpcs\Task\PhpcsLintInput;
use Codeception\Util\Stub;

// @codingStandardsIgnoreStart
class PhpcsLintInputTest extends \Codeception\Test\Unit
    // @codingStandardsIgnoreEnd
{

    /**
     * @param string $name
     *
     * @return ReflectionMethod
     */
    protected static function getMethod($name)
    {
        $class = new ReflectionClass(PhpcsLintInput::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testGetSetOptions()
    {
        $options = [
            'stdinPath' => 'abc',
        ];
        $task = new PhpcsLintInput($options);

        $this->tester->assertEquals($options['stdinPath'], $task->getStdinPath());
    }

    /**
     * @return array
     */
    public function casesGetCommand()
    {
        return [
            'with content' => [
                "echo -n 'content-01' | phpcs --stdin-path='a.php'",
                [
                    'phpcsExecutable' => 'phpcs',
                    'stdinPath' => 'a.php',
                ],
                [
                    'fileName' => 'a.php',
                    'content' => 'content-01',
                    'command' => 'git show :a.php',
                ],
            ],
            'without content' => [
                "git show :a.php | phpcs --stdin-path='a.php'",
                [
                    'phpcsExecutable' => 'phpcs',
                    'stdinPath' => 'a.php',
                ],
                [
                    'fileName' => 'a.php',
                    'content' => null,
                    'command' => "git show :a.php",
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesGetCommand
     */
    public function testGetCommand($expected, array $options, array $currentFile)
    {
        /** @var \Cheppers\Robo\Phpcs\Task\PhpcsLintInput $task */
        $task = Stub::construct(
            PhpcsLintInput::class,
            [$options],
            [
                'currentFile' => $currentFile,
            ]
        );
        $method = static::getMethod('getCommand');
        $task->setOptions($options);

        $this->tester->assertEquals($expected, $task->getCommand());
    }
}
