<?php

namespace Sweetchuck\Robo\Phpcs;

use Robo\Collection\CollectionBuilder;
use Sweetchuck\Robo\Phpcs\Task\PhpcsLintFiles;
use Sweetchuck\Robo\Phpcs\Task\PhpcsLintInput;
use League\Container\ContainerAwareInterface;
use Robo\Contract\OutputAwareInterface;
use Sweetchuck\Robo\Phpcs\Task\PhpcsParseXml;

trait PhpcsTaskLoader
{
    /**
     * Expose phpcs-lint task.
     *
     * @return \Sweetchuck\Robo\Phpcs\Task\PhpcsLintFiles|\Robo\Collection\CollectionBuilder
     */
    protected function taskPhpcsLintFiles(array $options = [])
    {
        /** @var \Sweetchuck\Robo\Phpcs\Task\PhpcsLintFiles $task */
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
     * @return \Sweetchuck\Robo\Phpcs\Task\PhpcsLintInput|\Robo\Collection\CollectionBuilder
     */
    protected function taskPhpcsLintInput(array $options = [])
    {
        /** @var \Sweetchuck\Robo\Phpcs\Task\PhpcsLintInput $task */
        $task = $this->task(PhpcsLintInput::class, $options);
        if ($this instanceof ContainerAwareInterface) {
            $task->setContainer($this->getContainer());
        }

        if ($this instanceof OutputAwareInterface) {
            $task->setOutput($this->output());
        }

        return $task;
    }

    /**
     * @return \Robo\Collection\CollectionBuilder|\Sweetchuck\Robo\Phpcs\Task\PhpcsParseXml
     */
    protected function taskPhpcsParseXml(array $options = []): CollectionBuilder
    {
        /** @var \Sweetchuck\Robo\Phpcs\Task\PhpcsParseXml $task */
        $task = $this->task(PhpcsParseXml::class);
        $task->setOptions($options);

        return $task;
    }
}
