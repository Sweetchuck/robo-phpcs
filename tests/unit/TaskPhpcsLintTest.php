<?php

use Cheppers\Robo\Phpcs\Task\TaskPhpcsLint;
use Codeception\Util\Stub;
use Robo\Robo;

/**
 * Class TaskPhpcsLintTest.
 *
 * @package Cheppers\Robo\Test\Task
 */
// @codingStandardsIgnoreStart
class TaskPhpcsLintTest extends \Codeception\Test\Unit
    // @codingStandardsIgnoreEnd
{
    use \Cheppers\Robo\Phpcs\Task\LoadTasks;
    use \Robo\TaskAccessor;

    /**
     * @var \League\Container\Container
     */
    protected $container = null;

    // @codingStandardsIgnoreStart
    protected function _before()
        // @codingStandardsIgnoreEnd
    {
        parent::_before();

        $this->container = new \League\Container\Container();
        Robo::setContainer($this->container);
        \Robo\Runner::configureContainer($this->container, null, new \Helper\Dummy\Output());
        $this->container->addServiceProvider(static::getPhpcsServiceProvider());
    }

    /**
     * @return \League\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

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
        $task = $this->taskPhpcsLint($options);

        static::assertEquals($expected, $task->getCommand());
    }

    public function testGetOptions()
    {
        $task = $this->taskPhpcsLint();
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
        $task = $this->taskPhpcsLint();
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
        $task = $this->taskPhpcsLint();
        static::assertEquals($expected, $task->getNormalizedOptions($options));
    }

    /**
     * @return array
     */
    public function casesRun()
    {
        return [
            'cli-0' => [
                0,
                '{"success": true}',
                [
                    'runMode' => 'cli',
                ],
            ],
            'cli-1' => [
                1,
                '{"success": true}',
                [
                    'runMode' => 'cli',
                ],
            ],
            'native-0' => [
                0,
                '{"success": true}',
                [
                    'runMode' => 'native',
                ],
            ],
            'native-1' => [
                1,
                '{"success": true}',
                [
                    'runMode' => 'native',
                ],
            ],
        ];
    }

    /**
     * This way cannot be tested those cases when the lint process failed.
     *
     * @dataProvider casesRun
     *
     * @param int $exitCode
     * @param string $stdOutput
     * @param array $options
     */
    public function testRun($exitCode, $stdOutput, $options)
    {
        $options += [
            'workingDirectory' => '.',
        ];

        /** @var \Cheppers\Robo\Phpcs\Task\TaskPhpcsLint $task */
        $task = Stub::construct(
            TaskPhpcsLint::class,
            [$options, []],
            [
                'processClass' => \Helper\Dummy\Process::class,
                'phpCodeSnifferCliClass' => \Helper\Dummy\PHP_CodeSniffer_CLI::class,
            ]
        );

        \Helper\Dummy\Process::$exitCode = $exitCode;
        \Helper\Dummy\Process::$stdOutput = $stdOutput;
        \Helper\Dummy\PHP_CodeSniffer_CLI::$numOfErrors = $exitCode ? 42 : 0;

        $task->setConfig(Robo::config());
        $task->setLogger($this->container->get('logger'));
        $result = $task->run();

        static::assertEquals($exitCode, $result->getExitCode());
        static::assertEquals(
            $options['workingDirectory'],
            \Helper\Dummy\Process::$instance->getWorkingDirectory()
        );

        /** @var \Helper\Dummy\Output $output */
        $output = $this->container->get('output');

        if ($options['runMode'] === 'cli') {
            static::assertContains($stdOutput, $output->output);
        }
    }

    public function testContainerInstance()
    {
        $task = $this->taskPhpcsLint();
        static::assertEquals(0, $task->getTaskExitCode());
    }
}
