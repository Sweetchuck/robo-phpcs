<?php

namespace Sweetchuck\Robo\Phpcs\Tests\Unit\Task;

use Sweetchuck\AssetJar\AssetJar;
use Sweetchuck\Robo\Phpcs\Task\PhpcsLintInput;
use Codeception\Util\Stub;
use Sweetchuck\Robo\Phpcs\Test\Helper\Dummy\Output as DummyOutput;
use Sweetchuck\Robo\Phpcs\Test\Helper\Dummy\Process as DummyProcess;
use Robo\Robo;

class PhpcsLintInputTest extends \Codeception\Test\Unit
{
    protected static function getMethod(string $name): \ReflectionMethod
    {
        $class = new \ReflectionClass(PhpcsLintInput::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @var \Sweetchuck\Robo\Phpcs\Test\UnitTester
     */
    protected $tester;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        DummyProcess::reset();
    }

    public function testGetSetOptions(): void
    {
        $options = [
            'stdinPath' => 'abc',
        ];
        $task = new PhpcsLintInput($options);

        $this->tester->assertEquals($options['stdinPath'], $task->getStdinPath());
    }

    public function casesGetCommand(): array
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
            'workingDirectory' => [
                "cd 'my/dir' && git show :a.php | phpcs --stdin-path='a.php'",
                [
                    'phpcsExecutable' => 'phpcs',
                    'workingDirectory' => 'my/dir',
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
    public function testGetCommand($expected, array $options, array $currentFile): void
    {
        /** @var \Sweetchuck\Robo\Phpcs\Task\PhpcsLintInput $task */
        $task = Stub::construct(
            PhpcsLintInput::class,
            [$options],
            [
                'currentFile' => $currentFile,
            ]
        );

        $this->tester->assertEquals($expected, $task->getCommand());
    }

    public function casesRun(): array
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
     */
    public function testRun(array $expected, array $options, array $files, array $properties = []): void
    {
        $container = Robo::createDefaultContainer();
        Robo::setContainer($container);

        $mainStdOutput = new DummyOutput();

        $properties += ['processClass' => DummyProcess::class];

        /** @var \Sweetchuck\Robo\Phpcs\Task\PhpcsLintInput $task */
        $task = Stub::construct(
            PhpcsLintInput::class,
            [$options, []],
            $properties
        );

        $processIndex = count(DummyProcess::$instances);
        foreach ($files as $file) {
            DummyProcess::$prophecy[$processIndex] = [
                'exitCode' => $file['lintExitCode'],
                'stdOutput' => $file['lintStdOutput'],
            ];

            $processIndex++;
        }

        $task->setLogger($container->get('logger'));
        $task->setOutput($mainStdOutput);

        $result = $task->run();

        $this->tester->assertEquals($expected['exitCode'], $result->getExitCode());

        $assetNamePrefix = $options['assetNamePrefix'] ?? '';

        /** @var \Sweetchuck\LintReport\ReportWrapperInterface $reportWrapper */
        $reportWrapper = $result["{$assetNamePrefix}report"];
        $this->tester->assertEquals(
            $expected['report'],
            $reportWrapper->getReport(),
            'Native report array'
        );
    }
}
