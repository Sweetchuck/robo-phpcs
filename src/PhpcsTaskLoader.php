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
     * @param array $options
     *
     * @return \Cheppers\Robo\Phpcs\Task\PhpcsLintFiles
     *   The task handler.
     */
    protected function taskPhpcsLintFiles(array $options = null)
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
     * @param array|null $options
     *
     * @return \Cheppers\Robo\Phpcs\Task\PhpcsLintInput
     */
    protected function taskPhpcsLintInput(array $options = null)
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
