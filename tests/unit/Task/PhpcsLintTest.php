<?php

use Cheppers\Robo\Phpcs\Task\PhpcsLint;
use Codeception\Util\Stub;
use Robo\Robo;

/**
 * Class TaskPhpcsLintTest.
 *
 * @package Cheppers\Robo\Test\Task
 */
// @codingStandardsIgnoreStart
class PhpcsLintTest extends \Codeception\Test\Unit
{
    // @codingStandardsIgnoreEnd

    /**
     * @return array
     */
    public function casesGetCommand()
    {
        return [
            'empty' => [
                'phpcs',
                [],
            ],

            'phpExecutable-string' => [
                'my-phpcs --colors',
                [
                    'phpcsExecutable' => 'my-phpcs',
                    'colors' => true,
                ],
            ],
            'colors-null' => [
                'phpcs',
                ['colors' => null],
            ],
            'colors-true' => [
                'phpcs --colors',
                ['colors' => true],
            ],
            'colors-false' => [
                'phpcs --no-colors',
                ['colors' => false],
            ],
            'reports-1' => [
                "phpcs --report='Default'",
                [
                    'reports' => [
                        'Default' => null,
                    ],
                ],
            ],
            'reports-2' => [
                "phpcs --report='Default' --report-'full'='/dev/null'",
                [
                    'reports' => [
                        'Default' => null,
                        'full' => '/dev/null',
                    ],
                ],
            ],
            'reportWidth' => [
                "phpcs --report-width='80'",
                ['reportWidth' => 80],
            ],
            'severity-string-empty' => [
                'phpcs',
                ['severity' => ''],
            ],
            'severity-false' => [
                'phpcs',
                ['severity' => false],
            ],
            'severity-null' => [
                'phpcs',
                ['severity' => null],
            ],
            'severity-integer-zero' => [
                "phpcs --severity='0'",
                ['severity' => 0],
            ],
            'severity-string-zero' => [
                "phpcs --severity='0'",
                ['severity' => '0'],
            ],
            'warning-severity-string-empty' => [
                'phpcs',
                ['warningSeverity' => ''],
            ],
            'warning-severity-false' => [
                'phpcs',
                ['warningSeverity' => false],
            ],
            'warning-severity-null' => [
                'phpcs',
                ['warningSeverity' => null],
            ],
            'warning-severity-integer-zero' => [
                "phpcs --warning-severity='0'",
                ['warningSeverity' => 0],
            ],
            'warning-severity-string-zero' => [
                "phpcs --warning-severity='0'",
                ['warningSeverity' => '0'],
            ],
            'error-severity-string-empty' => [
                'phpcs',
                ['errorSeverity' => ''],
            ],
            'error-severity-false' => [
                'phpcs',
                ['errorSeverity' => false],
            ],
            'error-severity-null' => [
                'phpcs',
                ['errorSeverity' => null],
            ],
            'error-severity-integer-zero' => [
                "phpcs --error-severity='0'",
                ['errorSeverity' => 0],
            ],
            'error-severity-string-zero' => [
                "phpcs --error-severity='0'",
                ['errorSeverity' => '0'],
            ],
            'standard-false' => [
                'phpcs',
                ['standard' => false],
            ],
            'standard-value' => [
                "phpcs --standard='Drupal'",
                ['standard' => 'Drupal'],
            ],
            'extensions-empty' => [
                "phpcs",
                [
                    'extensions' => ['php' => false],
                ],
            ],
            'extensions-single' => [
                "phpcs --extensions='php'",
                [
                    'extensions' => ['php' => true],
                ],
            ],
            'extensions-multi-1' => [
                "phpcs --extensions='php,js'",
                [
                    'extensions' => ['php' => true, 'js' => true],
                ],
            ],
            'extensions-multi-2' => [
                "phpcs --extensions='php,js'",
                [
                    'extensions' => ['php', 'js'],
                ],
            ],
            'sniffs-empty' => [
                "phpcs",
                [
                    'sniffs' => ['foo' => false],
                ],
            ],
            'sniffs-single-1' => [
                "phpcs --sniffs='foo'",
                [
                    'sniffs' => ['foo' => true],
                ],
            ],
            'sniffs-single-2' => [
                "phpcs --sniffs='foo'",
                [
                    'sniffs' => ['foo'],
                ],
            ],
            'sniffs-multi-1' => [
                "phpcs --sniffs='foo,bar'",
                [
                    'sniffs' => ['foo' => true, 'bar' => true, 'zed' => false],
                ],
            ],
            'sniffs-multi-2' => [
                "phpcs --sniffs='foo,bar,zed'",
                [
                    'sniffs' => ['foo', 'bar', 'zed'],
                ],
            ],
            'exclude-single-1' => [
                "phpcs --exclude='foo'",
                [
                    'exclude' => ['foo' => true],
                ],
            ],
            'exclude-single-2' => [
                "phpcs --exclude='foo'",
                [
                    'exclude' => ['foo'],
                ],
            ],
            'exclude-multi-1' => [
                "phpcs --exclude='foo,bar'",
                [
                    'exclude' => ['foo' => true, 'bar' => true, 'zed' => false],
                ],
            ],
            'exclude-multi-2' => [
                "phpcs --exclude='foo,bar,zed'",
                [
                    'exclude' => ['foo', 'bar', 'zed'],
                ],
            ],
            'ignore-single-1' => [
                "phpcs --ignore='foo'",
                [
                    'ignored' => ['foo' => true],
                ],
            ],
            'ignore-single-2' => [
                "phpcs --ignore='foo'",
                [
                    'ignored' => ['foo'],
                ],
            ],
            'ignore-multi-1' => [
                "phpcs --ignore='foo,bar'",
                [
                    'ignored' => ['foo' => true, 'bar' => true, 'zed' => false],
                ],
            ],
            'ignore-multi-2' => [
                "phpcs --ignore='foo,bar,zed'",
                [
                    'ignored' => ['foo', 'bar', 'zed'],
                ],
            ],
            'files-empty-1' => [
                "phpcs",
                [
                    'files' => ['foo' => false],
                ],
            ],
            'files-empty-2' => [
                "phpcs --colors",
                [
                    'colors' => true,
                    'files' => [],
                ],
            ],
            'files-single-1' => [
                "phpcs --colors 'foo'",
                [
                    'colors' => true,
                    'files' => ['foo' => true],
                ],
            ],
            'files-single-2' => [
                "phpcs --colors 'foo'",
                [
                    'colors' => true,
                    'files' => ['foo'],
                ],
            ],
            'files-multi-1' => [
                "phpcs --colors 'foo' 'bar'",
                [
                    'colors' => true,
                    'files' => ['foo' => true, 'bar' => true, 'zed' => false],
                ],
            ],
            'files-multi-2' => [
                "phpcs --colors 'foo' 'bar'",
                [
                    'colors' => true,
                    'files' => ['foo', 'bar'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesGetCommand
     *
     * @param string $expected
     * @param array $options
     */
    public function testGetCommand($expected, array $options)
    {
        $options += ['phpcsExecutable' => 'phpcs'];
        /** @var \Cheppers\Robo\Phpcs\Task\PhpcsLint $task */
        $task = Stub::construct(
            PhpcsLint::class,
            [$options, []]
        );

        static::assertEquals($expected, $task->getCommand());
    }

    public function testGetOptions()
    {
        /** @var \Cheppers\Robo\Phpcs\Task\PhpcsLint $task */
        $task = Stub::construct(PhpcsLint::class);

        $options = $task
            ->extensions(['foo', 'bar'])
            ->ignore(['a', 'b'])
            ->getOptions();

        static::assertEquals(
            [
                'extensions' => ['foo', 'bar'],
                'ignored' => ['a', 'b'],
            ],
            $options
        );
    }

    public function testRunMode()
    {
        /** @var \Cheppers\Robo\Phpcs\Task\PhpcsLint $task */
        $task = Stub::construct(PhpcsLint::class);
        try {
            $task->runMode('none');
            static::fail('TaskPhpcsLint::runMode() did not throw an exception.');
        } catch (\InvalidArgumentException $e) {
            static::assertEquals("Invalid argument: 'none'", $e->getMessage());
        }
    }

    /**
     * @return array
     */
    public function casesGetNormalizedConfig()
    {
        return [
            'colors-null' => [
                [],
                [
                    'colors' => null,
                ],
            ],
            'colors-true' => [
                [
                    'colors' => true
                ],
                [
                    'colors' => true,
                ],
            ],
            'standard-null' => [
                [],
                [
                    'standard' => null,
                ],
            ],
            'standard-value' => [
                [
                    'standard' => 'foo',
                ],
                [
                    'standard' => 'foo',
                ],
            ],
            'extensions-empty' => [
                [
                    'extensions' => [],
                ],
                [
                    'extensions' => [],
                ],
            ],
            'extensions-1' => [
                [
                    'extensions' => ['foo', 'bar'],
                ],
                [
                    'extensions' => ['foo' => true, 'bar' => true, 'zed' => false],
                ],
            ],
            'extensions-2' => [
                [
                    'extensions' => ['foo', 'bar'],
                ],
                [
                    'extensions' => ['foo', 'bar'],
                ],
            ],
            'files-empty' => [
                [
                    'files' => [],
                ],
                [
                    'files' => [],
                ],
            ],
            'files-1' => [
                [
                    'files' => ['foo', 'bar'],
                ],
                [
                    'files' => ['foo', 'bar'],
                ],
            ],
            'files-2' => [
                [
                    'files' => ['foo', 'bar'],
                ],
                [
                    'files' => ['foo', 'bar'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesGetNormalizedConfig
     *
     * @param array $expected
     * @param array $options
     */
    public function testGetNormalizedConfig(array $expected, array $options)
    {
        /** @var \Cheppers\Robo\Phpcs\Task\PhpcsLint $task */
        $task = Stub::construct(PhpcsLint::class);
        static::assertEquals($expected, $task->getNormalizedOptions($options));
    }

    /**
     * @return array
     */
    public function casesRun()
    {
        $reportBase = [
            'totals' => [
                'errors' => 0,
                'warnings' => 0,
            ],
            'files' => [
                'psr2.invalid.php' => [
                    'errors' => 0,
                    'warnings' => 0,
                    'messages' => [],
                ],
            ],
        ];

        $messageWarning = [
            'message' => 'M1',
            'source' => 'S1',
            'severity' => 4,
            'type' => 'WARNING',
            'line' => 2,
            'column' => 2,
            'fixable' => true,
        ];

        $messageError = [
            'message' => 'M1',
            'source' => 'S1',
            'severity' => 5,
            'type' => 'ERROR',
            'line' => 1,
            'column' => 1,
            'fixable' => true,
        ];

        $label_pattern = '%d; failOn: %s; E: %d; W: %d; exitCode: %d; runMode: %s; withJar: %s;';
        $cases = [];

        $combinations = [
            ['e' => true, 'w' => true, 'f' => 'never', 'c' => 0],
            ['e' => true, 'w' => false, 'f' => 'never', 'c' => 0],
            ['e' => false, 'w' => true, 'f' => 'never', 'c' => 0],
            ['e' => false, 'w' => false, 'f' => 'never', 'c' => 0],

            ['e' => true, 'w' => true, 'f' => 'warning', 'c' => 1],
            ['e' => true, 'w' => false, 'f' => 'warning', 'c' => 1],
            ['e' => false, 'w' => true, 'f' => 'warning', 'c' => 1],
            ['e' => false, 'w' => false, 'f' => 'warning', 'c' => 0],

            ['e' => true, 'w' => true, 'f' => 'error', 'c' => 1],
            ['e' => true, 'w' => false, 'f' => 'error', 'c' => 1],
            ['e' => false, 'w' => true, 'f' => 'error', 'c' => 0],
            ['e' => false, 'w' => false, 'f' => 'error', 'c' => 0],
        ];

        $i = 0;
        foreach (['cli', 'native'] as $runMode) {
            foreach ([true, false] as $withJar) {
                $withJarStr = $withJar ? 'true' : 'false';
                foreach ($combinations as $c) {
                    $i++;
                    $report = $reportBase;

                    if ($c['e']) {
                        $report['totals']['errors'] = 1;
                        $report['files']['a.php']['errors'] = 1;
                        $report['files']['a.php']['messages'][] = $messageError;
                    }

                    if ($c['w']) {
                        $report['totals']['warnings'] = 1;
                        $report['files']['a.php']['warnings'] = 1;
                        $report['files']['a.php']['messages'][] = $messageWarning;
                    }

                    $label = sprintf($label_pattern, $i, $c['f'], $c['e'], $c['w'], $c['c'], $runMode, $withJarStr);
                    $cases[$label] = [
                        $c['c'],
                        [
                            'failOn' => $c['f'],
                            'runMode' => $runMode,
                        ],
                        $withJar,
                        json_encode($report)
                    ];
                }
            }
        }

        return $cases;
    }

    /**
     * @dataProvider casesRun
     *
     * @param int $exitCode
     * @param array $options
     * @param bool $withJar
     * @param string $expectedStdOutput
     */
    public function testRun($exitCode, $options, $withJar, $expectedStdOutput)
    {
        $container = new \League\Container\Container();
        $config = new \Robo\Config();
        $mainStdOutput = new \Helper\Dummy\Output();
        \Robo\Robo::configureContainer($container);
        \Robo\Robo::setContainer($container, null, $mainStdOutput);

        $options += [
            'workingDirectory' => '.',
            'assetJarMapping' => ['report' => ['phpcsLintRun', 'report']],
            'reports' => [
                'json' => null,
            ],
        ];

        /** @var \Cheppers\Robo\Phpcs\Task\PhpcsLint $task */
        $task = Stub::construct(
            PhpcsLint::class,
            [$options, []],
            [
                'processClass' => \Helper\Dummy\Process::class,
                'phpCodeSnifferCliClass' => \Helper\Dummy\PHP_CodeSniffer_CLI::class,
            ]
        );

        \Helper\Dummy\Process::$exitCode = $exitCode;
        \Helper\Dummy\Process::$stdOutput = $expectedStdOutput;
        \Helper\Dummy\PHP_CodeSniffer_CLI::$numOfErrors = $exitCode ? 42 : 0;
        \Helper\Dummy\PHP_CodeSniffer_CLI::$stdOutput = $expectedStdOutput;

        $task->setLogger($container->get('logger'));
        $task->setOutput($mainStdOutput);

        $assetJar = null;
        if ($withJar) {
            $assetJar = new \Cheppers\AssetJar\AssetJar();
            $task->setAssetJar($assetJar);
        }

        $result = $task->run();

        static::assertEquals(
            $exitCode,
            $result->getExitCode(),
            'Exit code is different than the expected.'
        );

        if ($options['runMode'] === 'cli') {
            static::assertEquals(
                $options['workingDirectory'],
                \Helper\Dummy\Process::$instance->getWorkingDirectory()
            );
        }

        if ($withJar) {
            static::assertEquals(
                json_decode($expectedStdOutput, true),
                $assetJar->getValue(['phpcsLintRun', 'report']),
                'Output equals'
            );
        } else {
            static::assertContains(
                $expectedStdOutput,
                $mainStdOutput->output,
                'Output contains'
            );
        }
    }
}
