<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Phpcs;

use Robo\Collection\CollectionBuilder;
use Sweetchuck\Robo\Phpcs\Task\PhpcsLintFiles;
use Sweetchuck\Robo\Phpcs\Task\PhpcsLintInput;
use Sweetchuck\Robo\Phpcs\Task\PhpcsParseXml;

trait PhpcsTaskLoader
{
    /**
     * @return \Sweetchuck\Robo\Phpcs\Task\PhpcsLintFiles|\Robo\Collection\CollectionBuilder
     */
    protected function taskPhpcsLintFiles(array $options = []): CollectionBuilder
    {
        /** @var \Sweetchuck\Robo\Phpcs\Task\PhpcsLintFiles $task */
        $task = $this->task(PhpcsLintFiles::class);
        $task->setOptions($options);

        return $task;
    }

    /**
     * @return \Sweetchuck\Robo\Phpcs\Task\PhpcsLintInput|\Robo\Collection\CollectionBuilder
     */
    protected function taskPhpcsLintInput(array $options = []): CollectionBuilder
    {
        /** @var \Sweetchuck\Robo\Phpcs\Task\PhpcsLintInput $task */
        $task = $this->task(PhpcsLintInput::class);
        $task->setOptions($options);

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
