<?php

namespace Cheppers\Robo\Phpcs\Task;

/**
 * Class PhpcsTask.
 *
 * @package Cheppers\Robo\Phpcs\Task
 */
trait LoadTasks
{
    /**
     * Expose phpcs-lint task.
     *
     * @param array $options
     *
     * @return \Cheppers\Robo\Phpcs\Task\PhpcsLint
     *   The task handler.
     */
    protected function taskPhpcsLint(array $options = null)
    {
        return $this->task(PhpcsLint::class, $options);
    }
}
