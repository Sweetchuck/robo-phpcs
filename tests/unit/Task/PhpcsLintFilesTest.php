<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Phpcs\Tests\Unit\Task;

use PHPUnit\Framework\SkippedTestSuiteError;
use Robo\Robo;
use Sweetchuck\Robo\Phpcs\Task\PhpcsLintFiles;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyOutput;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcess;

class PhpcsLintFilesTest extends TestBase
{
    public function testGetSetLintReporters(): void
    {
        $task = (new PhpcsLintFiles())
            ->setOptions([
                'lintReporters' => [
                    'aKey' => 'aValue',
                ],
            ])
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

    public function testGetSetAssetNamePrefix()
    {
        $task = (new PhpcsLintFiles())
            ->setOptions(['assetNamePrefix' => 'a']);
        $this->tester->assertEquals('a', $task->getAssetNamePrefix());

        $task->setAssetNamePrefix('b');
        $this->tester->assertEquals('b', $task->getAssetNamePrefix());
    }

    public function testGetSetWorkingDirectory()
    {
        $task = (new PhpcsLintFiles())
            ->setOptions(['workingDirectory' => 'a']);
        $this->tester->assertEquals('a', $task->getWorkingDirectory());

        $task->setWorkingDirectory('b');
        $this->tester->assertEquals('b', $task->getWorkingDirectory());
    }

    public function testGetSetPhpcsExecutable(): void
    {
        $task = new PhpcsLintFiles();
        $this->tester->assertEquals('', $task->getPhpcsExecutable(), 'default value');

        $task = (new PhpcsLintFiles())
            ->setOptions(['phpcsExecutable' => 'a']);
        $this->tester->assertEquals('a', $task->getPhpcsExecutable(), 'set with setOptions()');

        $task->setPhpcsExecutable('b');
        $this->tester->assertEquals('b', $task->getPhpcsExecutable(), 'normal');
    }

    public function testGetSetReport(): void
    {
        $task = new PhpcsLintFiles();
        $this->tester->assertNull($task->getReport('full'), 'default value');

        $task = (new PhpcsLintFiles())
            ->setOptions(['reports' => ['full' => 'a']]);
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
            'cache-empty' => [
                'phpcs',
                ['cache' => ''],
            ],
            'cache-somethings' => [
                "phpcs --cache='my-file.txt'",
                ['cache' => 'my-file.txt'],
            ],
            'no-cache-false' => [
                'phpcs',
                ['noCache' => false],
            ],
            'no-cache-true' => [
                'phpcs --no-cache',
                ['noCache' => true],
            ],
            'tab-width null' => [
                'phpcs',
                ['tabWidth' => null],
            ],
            'tab-width zero' => [
                "phpcs --tab-width='0'",
                ['tabWidth' => 0],
            ],
            'tab-width 1' => [
                "phpcs --tab-width='1'",
                ['tabWidth' => 1],
            ],
            'base-path empty' => [
                'phpcs',
                ['basePath' => ''],
            ],
            'base-path something' => [
                "phpcs --basepath='/foo/bar'",
                ['basePath' => '/foo/bar'],
            ],
            'bootstrap empty' => [
                'phpcs',
                ['bootstrap' => []],
            ],
            'bootstrap vector' => [
                "phpcs --bootstrap='a.php,b.php'",
                ['bootstrap' => ['a.php', 'b.php']],
            ],
            'bootstrap boolean' => [
                "phpcs --bootstrap='a.php,c.php'",
                ['bootstrap' => ['a.php' => true, 'b.php' => false, 'c.php' => true]],
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
            'warning-severity-null' => [
                'phpcs',
                ['warningSeverity' => null],
            ],
            'warning-severity-integer-zero' => [
                "phpcs --warning-severity='0'",
                ['warningSeverity' => 0],
            ],
            'error-severity-null' => [
                'phpcs',
                ['errorSeverity' => null],
            ],
            'error-severity-integer-zero' => [
                "phpcs --error-severity='0'",
                ['errorSeverity' => 0],
            ],
            'error-severity-integer-one' => [
                "phpcs --error-severity='1'",
                ['errorSeverity' => 1],
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
            'encoding empty' => [
                'phpcs',
                ['encoding' => ''],
            ],
            'encoding something' => [
                "phpcs --encoding='foo'",
                ['encoding' => 'foo'],
            ],
            'parallel null' => [
                'phpcs',
                ['parallel' => null],
            ],
            'parallel zero' => [
                "phpcs --parallel='0'",
                ['parallel' => 0],
            ],
            'parallel 1' => [
                "phpcs --parallel='1'",
                ['parallel' => 1],
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
            'ignore-annotations false' => [
                'phpcs',
                ['ignoreAnnotations' => false],
            ],
            'ignore-annotations true' => [
                'phpcs --ignore-annotations',
                ['ignoreAnnotations' => true],
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
            'all-in-one' => [
                implode(' ', [
                    'phpcs',
                    '--ignore-annotations',
                    '--colors',
                    "--cache='cache.txt'",
                    "--tab-width='4'",
                    "--basepath='bp1'",
                    "--severity='1'",
                    "--error-severity='2'",
                    "--warning-severity='3'",
                    "--encoding='en1'",
                    "--parallel='4'",
                    "--bootstrap='bs1,bs2'",
                    "--standard='s1,s3'",
                    "--sniffs='sn1,sn2'",
                    "--exclude='ex1,ex2'",
                    "--extensions='e1,e2'",
                    "-- 'a.php' 'b.php'",
                ]),
                [
                    'ignoreAnnotations' => true,
                    'colors' => true,
                    'cache' => 'cache.txt',
                    'tabWidth' => 4,
                    'basePath' => 'bp1',
                    'severity' => 1,
                    'errorSeverity' => 2,
                    'warningSeverity' => 3,
                    'encoding' => 'en1',
                    'parallel' => 4,
                    'bootstrap' => ['bs1', 'bs2'],
                    'standards' => ['s1' => true, 's2' => false, 's3' => true],
                    'sniffs' => ['sn1', 'sn2'],
                    'exclude' => ['ex1', 'ex2'],
                    'extensions' => ['e1', 'e2'],
                    'files' => ['a.php', 'b.php'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesGetCommand
     */
    public function testGetCommand(string $expected, array $options): void
    {
        $task = (new PhpcsLintFiles())
            ->setOptions($options + ['phpcsExecutable' => 'phpcs']);

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

        $labelPattern = '%d; failOn: %s; E: %d; W: %d; exitCode: %d;';
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

            $label = sprintf($labelPattern, $i, $c['f'], $c['e'], $c['w'], $c['c']);
            $cases[$label] = [
                $c['c'],
                [
                    'failOn' => $c['f'],
                ],
                json_encode($report)
            ];
        }

        return $cases;
    }

    /**
     * @dataProvider casesRun
     */
    public function testRun(int $exitCode, array $options, string $expectedStdOutput): void
    {
        $mainStdOutput = new DummyOutput([]);

        $options += [
            'reports' => [
                'json' => null,
            ],
        ];

        $task = $this->taskBuilder->taskPhpcsLintFiles();
        $task->setProcessClass(DummyProcess::class);
        $task->setContainer($this->container);

        $task->setOptions($options);

        $processIndex = count(DummyProcess::$instances);

        DummyProcess::$prophecy[$processIndex] = [
            'exitCode' => $exitCode,
            'stdOutput' => $expectedStdOutput,
        ];

        $task->setLogger($this->container->get('logger'));
        //$task->setOutput($mainStdOutput);

        $result = $task->run();

        $this->tester->assertSame(
            $exitCode,
            $result->getExitCode(),
            'Exit code is different than the expected.',
        );

        $assetNamePrefix = $options['assetNamePrefix'] ?? '';

        /** @var \Sweetchuck\LintReport\ReportWrapperInterface $reportWrapper */
        $reportWrapper = $result["{$assetNamePrefix}report"];
        $this->tester->assertSame(
            json_decode($expectedStdOutput, true),
            $reportWrapper->getReport(),
            'Output equals',
        );

        /** @var \Sweetchuck\Codeception\Module\RoboTaskRunner\DummyOutput $output */
        $output = $this->container->get('output');

        $this->tester->assertStringContainsString(
            $expectedStdOutput,
            $output->output,
            'Output contains',
        );
    }
}
