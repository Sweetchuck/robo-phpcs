<?php
/**
 * @file
 * ${FILE_DESCRIPTION}${CARET}
 */

namespace Cheppers\Robo\Test\Task;

use Cheppers\Robo\Task\Phpcs\LoadTasks as PhpcsLoadTasks;
use League\Container\Container;
use Robo\Config;
use Robo\Container\RoboContainer;
use Robo\Runner;

/**
 * Class TaskPhpcsLintTest.
 *
 * @package Cheppers\Robo\Test\Task
 *
 * covers \Cheppers\Robo\Task\Phpcs\TaskPhpcsLint
 */
class TaskPhpcsLintTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        Config::setContainer(new RoboContainer());
        Runner::addServiceProviders(Config::getContainer());
        Config::getContainer()->addServiceProvider(PhpcsLoadTasks::getPhpcsServiceProvider());
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
            'standard-false' => [
                "phpcs",
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
     * @param array $config
     */
    public function testGetCommand($expected, array $config)
    {
        /** @var Container $container */
        $container = Config::getContainer();
        /** @var \Cheppers\Robo\Task\Phpcs\TaskPhpcsLint $task */
        $task = $container->get('taskPhpcsLint', [$config]);
        $task->phpcsExecutable('phpcs');

        static::assertEquals($expected, $task->getCommand());
    }

    public function testGetConfig()
    {
        /** @var Container $container */
        $container = Config::getContainer();
        /** @var \Cheppers\Robo\Task\Phpcs\TaskPhpcsLint $task */
        $task = $container->get('taskPhpcsLint');
        $config = $task
            ->extensions(['foo', 'bar'])
            ->ignore(['a', 'b'])
            ->getConfig();

        static::assertEquals(
            [
                'extensions' => ['foo', 'bar'],
                'ignored' => ['a', 'b'],
            ],
            $config
        );
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
     * @param array $config
     */
    public function testGetNormalizedConfig(array $expected, array $config)
    {
        /** @var \Cheppers\Robo\Task\Phpcs\TaskPhpcsLint $task */
        $task = Config::getContainer()->get('taskPhpcsLint');
        static::assertEquals($expected, $task->getNormalizedConfig($config));
    }
}
