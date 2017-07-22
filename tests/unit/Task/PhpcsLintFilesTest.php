<?php

namespace Cheppers\Robo\Phpcs\Tests\Unit\Task;

use Cheppers\AssetJar\AssetJar;
use Cheppers\Robo\Phpcs\Task\PhpcsLintFiles;
use Codeception\Util\Stub;

class PhpcsLintFilesTest extends \Codeception\Test\Unit
{
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

    public function testGetSetLintReporters(): void
    {
        $task = new PhpcsLintFiles([
            'lintReporters' => [
                'aKey' => 'aValue',
            ],
        ]);

        $task
            ->addLintReporter('bKey', 'bValue')
            ->addLintReporter('cKey', 'cValue')
            ->removeLintReporter('bKey');

        $this->tester->assertEquals(
            [
                'aKey' => 'aValue',
                'cKey' => 'cValue',
            ],
            $task->getLintReporters()
        );
    }

    public function testGetSetWorkingDirectory()
    {
        $task = new PhpcsLintFiles(['workingDirectory' => 'a']);
        $this->tester->assertEquals('a', $task->getWorkingDirectory());

        $task->setWorkingDirectory('b');
        $this->tester->assertEquals('b', $task->getWorkingDirectory());
    }

    public function testGetSetPhpcsExecutable(): void
    {
        $task = new PhpcsLintFiles();
        $this->tester->assertEquals('bin/phpcs', $task->getPhpcsExecutable(), 'default value');

        $task = new PhpcsLintFiles(['phpcsExecutable' => 'a']);
        $this->tester->assertEquals('a', $task->getPhpcsExecutable(), 'set in constructor');

        $task->setPhpcsExecutable('b');
        $this->tester->assertEquals('b', $task->getPhpcsExecutable(), 'normal');
    }

    public function testGetSetAssetJar(): void
    {
        $task = new PhpcsLintFiles();
        $this->tester->assertNull($task->getAssetJar(), 'default value');

        $assetJar1 = new AssetJar();
        $task = new PhpcsLintFiles(['assetJar' => $assetJar1]);
        $this->tester->assertEquals($assetJar1, $task->getAssetJar(), 'set in constructor');

        $assetJar2 = new AssetJar();
        $task->setAssetJar($assetJar2);
        $this->tester->assertEquals($assetJar2, $task->getAssetJar(), 'normal');
    }

    public function testGetSetReport(): void
    {
        $task = new PhpcsLintFiles();
        $this->tester->assertNull($task->getReport('full'), 'default value');

        $task = new PhpcsLintFiles(['reports' => ['full' => 'a']]);
        $this->tester->assertEquals('a', $task->getReport('full'), 'set in constructor');

        $task->setReport('full', 'b');
        $this->tester->assertEquals('b', $task->getReport('full'), 'normal');
    }

    public function casesGetCommand(): array
    {
        return [
            'empty' => [
                'phpcs',
                [],
            ],
            'workingDirectory' => [
                "cd 'my/sub/dir' && phpcs",
                [
                    'workingDirectory' => 'my/sub/dir',
                ],
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
            'standards-empty' => [
                'phpcs',
                ['standards' => []],
            ],
            'standards-vector' => [
                "phpcs --standard='a,b'",
                ['standards' => ['a', 'b', 'a']],
            ],
            'standards-boolean' => [
                "phpcs --standard='a,c'",
                ['standards' => ['a' => true, 'b' => false, 'c' => true]],
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
                "phpcs --colors -- 'foo'",
                [
                    'colors' => true,
                    'files' => ['foo' => true],
                ],
            ],
            'files-single-2' => [
                "phpcs --colors -- 'foo'",
                [
                    'colors' => true,
                    'files' => ['foo'],
                ],
            ],
            'files-multi-1' => [
                "phpcs --colors -- 'foo' 'bar'",
                [
                    'colors' => true,
                    'files' => ['foo' => true, 'bar' => true, 'zed' => false],
                ],
            ],
            'files-multi-2' => [
                "phpcs --colors -- 'foo' 'bar'",
                [
                    'colors' => true,
                    'files' => ['foo', 'bar'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesGetCommand
     */
    public function testGetCommand(string $expected, array $options): void
    {
        $task = new PhpcsLintFiles($options + ['phpcsExecutable' => 'phpcs']);

        $this->tester->assertEquals($expected, $task->getCommand());
    }

    public function casesRun(): array
    {
        $reportBase = [
            'totals' => [
                'errors' => 0,
                'warnings' => 0,
                'fixable' => 0,
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

        $label_pattern = '%d; failOn: %s; E: %d; W: %d; exitCode: %d; withJar: %s;';
        $cases = [];

        $combinations = [
            ['e' => true, 'w' => true, 'f' => 'never', 'c' => 0],
            ['e' => true, 'w' => false, 'f' => 'never', 'c' => 0],
            ['e' => false, 'w' => true, 'f' => 'never', 'c' => 0],
            ['e' => false, 'w' => false, 'f' => 'never', 'c' => 0],

            ['e' => true, 'w' => true, 'f' => 'warning', 'c' => 2],
            ['e' => true, 'w' => false, 'f' => 'warning', 'c' => 2],
            ['e' => false, 'w' => true, 'f' => 'warning', 'c' => 1],
            ['e' => false, 'w' => false, 'f' => 'warning', 'c' => 0],

            ['e' => true, 'w' => true, 'f' => 'error', 'c' => 2],
            ['e' => true, 'w' => false, 'f' => 'error', 'c' => 2],
            ['e' => false, 'w' => true, 'f' => 'error', 'c' => 0],
            ['e' => false, 'w' => false, 'f' => 'error', 'c' => 0],
        ];

        $i = 0;
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

                $label = sprintf($label_pattern, $i, $c['f'], $c['e'], $c['w'], $c['c'], $withJarStr);
                $cases[$label] = [
                    $c['c'],
                    [
                        'failOn' => $c['f'],
                    ],
                    $withJar,
                    json_encode($report)
                ];
            }
        }

        return $cases;
    }

    /**
     * @dataProvider casesRun
     */
    public function testRun(int $exitCode, array $options, bool $withJar, string $expectedStdOutput): void
    {
        $container = \Robo\Robo::createDefaultContainer();
        \Robo\Robo::setContainer($container);

        $mainStdOutput = new \Helper\Dummy\Output();

        $options += [
            'assetJarMapping' => ['report' => ['phpcsLintRun', 'report']],
            'reports' => [
                'json' => null,
            ],
        ];

        /** @var \Cheppers\Robo\Phpcs\Task\PhpcsLintFiles $task */
        $task = Stub::construct(
            PhpcsLintFiles::class,
            [$options, []],
            [
                'processClass' => \Helper\Dummy\Process::class,
                'phpCodeSnifferCliClass' => \Helper\Dummy\PHP_CodeSniffer_CLI::class,
            ]
        );

        $processIndex = count(\Helper\Dummy\Process::$instances);

        \Helper\Dummy\Process::$prophecy[$processIndex] = [
            'exitCode' => $exitCode,
            'stdOutput' => $expectedStdOutput,
        ];

        \Helper\Dummy\PHP_CodeSniffer_CLI::$numOfErrors = $exitCode ? 42 : 0;
        \Helper\Dummy\PHP_CodeSniffer_CLI::$stdOutput = $expectedStdOutput;

        $task->setLogger($container->get('logger'));
        $task->setOutput($mainStdOutput);

        $assetJar = null;
        if ($withJar) {
            $assetJar = new AssetJar();
            $task->setAssetJar($assetJar);
        }

        $result = $task->run();

        $this->tester->assertEquals(
            $exitCode,
            $result->getExitCode(),
            'Exit code is different than the expected.'
        );

        if ($withJar) {
            /** @var \Cheppers\LintReport\ReportWrapperInterface $reportWrapper */
            $reportWrapper = $assetJar->getValue(['phpcsLintRun', 'report']);
            $this->tester->assertEquals(
                json_decode($expectedStdOutput, true),
                $reportWrapper->getReport(),
                'Output equals'
            );
        } else {
            $this->tester->assertContains(
                $expectedStdOutput,
                $mainStdOutput->output,
                'Output contains'
            );
        }
    }
}
