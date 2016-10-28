<?php

use Cheppers\AssetJar\AssetJar;
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

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        \Helper\Dummy\Process::reset();
    }

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

        $this->tester->assertEquals($expected, $task->getCommand());
    }

    /**
     * @return array
     */
    public function casesGetJarValueOrLocal()
    {
        return [
            'without jar' => [
                ['a.php', 'b.php'],
                'files',
                ['files' => ['a.php', 'b.php']],
                [],
            ],
            'with jar' => [
                ['c.php', 'd.php'],
                'files',
                [
                    'files' => ['a.php', 'b.php'],
                    'assetJarMapping' => ['files' => ['l1', 'l2']],
                ],
                [
                    'l1' => [
                        'l2' => ['c.php', 'd.php'],
                    ],
                ],
            ],
            'non-exists' => [
                null,
                'non-exists',
                [
                    'files' => ['a.php', 'b.php'],
                    'assetJarMapping' => ['files' => ['l1', 'l2']],
                ],
                [
                    'l1' => [
                        'l2' => ['c.php', 'd.php'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesGetJarValueOrLocal
     *
     * @param mixed $expected
     * @param string $itemName
     * @param array $options
     * @param array $jarValue
     */
    public function testGetJarValueOrLocal($expected, $itemName, array $options, array $jarValue)
    {
        /** @var \Cheppers\Robo\Phpcs\Task\PhpcsLintInput $task */
        $task = Stub::construct(
            PhpcsLintInput::class,
            [$options],
            []
        );
        $method = static::getMethod('getJarValueOrLocal');
        $task->setAssetJar(new AssetJar($jarValue));

        $this->tester->assertEquals($expected, $method->invoke($task, $itemName));
    }

    /**
     * @return array
     */
    public function casesRun()
    {
        $files = [
            'empty' => [
                'totals' => [
                    'errors' => 0,
                    'warnings' => 0,
                    'fixable' => 0,
                ],
                'files' => [],
            ],
            'w1' => [
                'totals' => [
                    'errors' => 0,
                    'warnings' => 1,
                    'fixable' => 0,
                ],
                'files' => [
                    'w1.js' => [
                        'errors' => 0,
                        'warnings' => 1,
                        'messages' => [
                            [
                                'column' => 1,
                                'fixable' => false,
                                'line' => 3,
                                'message' => 'Dummy error message',
                                'severity' => 4,
                                'source' => 'PSR1.Classes.ClassDeclaration.MissingNamespace',
                                'type' => 'WARNING',
                            ]
                        ],
                    ],
                ],
            ],
            'w2' => [
                'totals' => [
                    'errors' => 0,
                    'warnings' => 1,
                    'fixable' => 0,
                ],
                'files' => [
                    'w2.js' => [
                        'errors' => 0,
                        'warnings' => 1,
                        'messages' => [
                            [
                                'column' => 1,
                                'fixable' => false,
                                'line' => 3,
                                'message' => 'Dummy error message',
                                'severity' => 4,
                                'source' => 'PSR1.Classes.ClassDeclaration.MissingNamespace',
                                'type' => 'WARNING',
                            ]
                        ],
                    ],
                ],
            ],
            'e1' => [
                'totals' => [
                    'errors' => 1,
                    'warnings' => 0,
                    'fixable' => 0,
                ],
                'files' => [
                    'e1.js' => [
                        'errors' => 1,
                        'warnings' => 0,
                        'messages' => [
                            [
                                'column' => 1,
                                'fixable' => false,
                                'line' => 3,
                                'message' => 'Dummy error message',
                                'severity' => 5,
                                'source' => 'PSR1.Classes.ClassDeclaration.MissingNamespace',
                                'type' => 'ERROR',
                            ]
                        ],
                    ],
                ],
            ],
        ];

        return [
            'empty' => [
                [
                    'exitCode' => 0,
                    'report' => $files['empty'],
                    'files' => [],
                ],
                [
                    'format' => 'json',
                    'failOn' => 'warning',
                ],
                [],
            ],
            'w0 never' => [
                [
                    'exitCode' => 0,
                    'report' => [
                        'totals' => [
                            'errors' => $files['w1']['totals']['errors'] + $files['w2']['totals']['errors'],
                            'warnings' => $files['w1']['totals']['warnings'] + $files['w2']['totals']['warnings'],
                            'fixable' => $files['w1']['totals']['fixable'] + $files['w2']['totals']['fixable'],
                        ],
                        'files' => $files['w1']['files'] + $files['w2']['files'],
                    ],
                ],
                [
                    'format' => 'json',
                    'failOn' => 'never',
                    'files' => [
                        'w1.js' => '',
                        'w2.js' => '',
                    ],
                ],
                [
                    'w1.js' => [
                        'lintExitCode' => 1,
                        'lintStdOutput' => json_encode($files['w1'], true),
                        'report' => $files['w1'],
                    ],
                    'w2.js' => [
                        'lintExitCode' => 1,
                        'lintStdOutput' => json_encode($files['w2'], true),
                        'report' => $files['w2'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesRun
     *
     * @param array $expected
     * @param array $options
     * @param array $properties
     */
    public function testRun(array $expected, array $options, array $files, array $properties = [])
    {
        $container = \Robo\Robo::createDefaultContainer();
        \Robo\Robo::setContainer($container);

        $mainStdOutput = new \Helper\Dummy\Output();

        $properties += ['processClass' => \Helper\Dummy\Process::class];

        /** @var \Cheppers\Robo\Phpcs\Task\PhpcsLintInput $task */
        $task = Stub::construct(
            PhpcsLintInput::class,
            [$options, []],
            $properties
        );

        $processIndex = count(\Helper\Dummy\Process::$instances);
        foreach ($files as $file) {
            \Helper\Dummy\Process::$prophecy[$processIndex] = [
                'exitCode' => $file['lintExitCode'],
                'stdOutput' => $file['lintStdOutput'],
            ];

            $processIndex++;
        }

        $task->setLogger($container->get('logger'));
        $task->setOutput($mainStdOutput);

        $result = $task->run();

        $this->tester->assertEquals($expected['exitCode'], $result->getExitCode());

        /** @var \Cheppers\LintReport\ReportWrapperInterface $reportWrapper */
        $reportWrapper = $result['report'];
        $this->tester->assertEquals($expected['report'], $reportWrapper->getReport());
    }
}
