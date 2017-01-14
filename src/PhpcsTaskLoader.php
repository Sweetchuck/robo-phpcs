<?php

namespace Cheppers\Robo\Phpcs;

use Cheppers\Robo\Phpcs\Task\PhpcsLintFiles;
use Cheppers\Robo\Phpcs\Task\PhpcsLintInput;
use League\Container\ContainerAwareInterface;
use Robo\Contract\OutputAwareInterface;

/**
 * Class PhpcsTask.
 *
 * @package Cheppers\Robo\Phpcs\Task
 */
trait PhpcsTaskLoader
{
    /**
     * Expose phpcs-lint task.
     *
     * @return \Cheppers\Robo\Phpcs\Task\PhpcsLintFiles|\Robo\Collection\CollectionBuilder
     */
    protected function taskPhpcsLintFiles(array $options = [])
    {
        /** @var \Cheppers\Robo\Phpcs\Task\PhpcsLintFiles $task */
        $task = $this->task(PhpcsLintFiles::class, $options);
        if ($this instanceof ContainerAwareInterface) {
            $task->setContainer($this->getContainer());
        }

        if ($this instanceof OutputAwareInterface) {
            $task->setOutput($this->output());
        }

        return $task;
    }

    /**
     * @return \Cheppers\Robo\Phpcs\Task\PhpcsLintInput|\Robo\Collection\CollectionBuilder
     */
    protected function taskPhpcsLintInput(array $options = [])
    {
        /** @var \Cheppers\Robo\Phpcs\Task\PhpcsLintInput $task */
        $task = $this->task(PhpcsLintInput::class, $options);
        if ($this instanceof ContainerAwareInterface) {
            $task->setContainer($this->getContainer());
        }

        if ($this instanceof OutputAwareInterface) {
            $task->setOutput($this->output());
        }

        return $task;
    }
}
