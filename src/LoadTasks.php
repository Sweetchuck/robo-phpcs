<?php

namespace Cheppers\Robo\Task\Phpcs;

use Robo\Container\SimpleServiceProvider;
use Robo\TaskAccessor;

/**
 * Class PhpcsTask.
 *
 * @package Cheppers\Robo\Task\Phpcs
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
     * @return \Cheppers\Robo\Task\Phpcs\TaskPhpcsLint
     *   The task handler.
     */
    protected function taskPhpcsLint(array $options = null)
    {
        return $this->task(__FUNCTION__, $options);
    }
}
