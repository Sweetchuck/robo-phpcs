<?php

namespace Cheppers\Robo\Phpcs\Task;

use Robo\Container\SimpleServiceProvider;
use Robo\TaskAccessor;

/**
 * Class PhpcsTask.
 *
 * @package Cheppers\Robo\Phpcs\Task
 */
trait LoadTasks
{
    /**
     * Return services.
     */
    public static function getPhpcsServiceProvider()
    {
        return new SimpleServiceProvider(
            [
                'taskPhpcsLint' => TaskPhpcsLint::class,
            ]
        );
    }

    /**
     * Expose phpcs-lint task.
     *
     * @param array $options
     *
     * @return \Cheppers\Robo\Phpcs\Task\TaskPhpcsLint
     *   The task handler.
     */
    protected function taskPhpcsLint(array $options = null)
    {
        return $this->task(__FUNCTION__, $options);
    }
}
