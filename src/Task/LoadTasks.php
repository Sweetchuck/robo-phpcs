<?php

namespace Cheppers\Robo\Phpcs\Task;

use League\Container\ContainerAwareInterface;
use Robo\Contract\OutputAwareInterface;

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
        /** @var PhpcsLint $task */
        $task = $this->task(PhpcsLint::class, $options);
        if ($this instanceof ContainerAwareInterface) {
            $task->setContainer($this->getContainer());
        }

        if ($this instanceof OutputAwareInterface) {
            $task->setOutput($this->output());
        }

        return $task;
    }
}
