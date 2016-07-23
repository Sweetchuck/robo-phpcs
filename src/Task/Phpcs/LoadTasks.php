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
                'taskPhpcsConfigSet' => TaskPhpcsConfigSet::class,
                'taskPhpcsConfigDelete' => TaskPhpcsConfigDelete::class,
                'taskPhpcsConfigShow' => TaskPhpcsConfigShow::class,
                'taskPhpcsBeautify' => TaskPhpcsBeautify::class,
            ]
        );
    }

    /**
     * Expose phpcs-lint task.
     *
     * @param array $config
     *
     * @return \Cheppers\Robo\Task\Phpcs\TaskPhpcsLint
     *   The task handler.
     */
    protected function taskPhpcsLint(array $config = null)
    {
        return $this->task(__FUNCTION__, $config);
    }

    /**
     * Expose phpcs-config-set task.
     *
     * @return \Cheppers\Robo\Task\Phpcs\TaskPhpcsConfigSet
     *   The task handler.
     */
    protected function taskPhpcsConfigSet()
    {
        return $this->task(__FUNCTION__);
    }

    /**
     * Expose phpcs-config-delete task.
     *
     * @return \Cheppers\Robo\Task\Phpcs\TaskPhpcsConfigDelete
     *   The task handler.
     */
    protected function taskPhpcsConfigDelete()
    {
        return $this->task(__FUNCTION__);
    }

    /**
     * Expose phpcs-config-show task.
     *
     * @return \Cheppers\Robo\Task\Phpcs\TaskPhpcsConfigShow
     *   The task handler.
     */
    protected function taskPhpcsConfigShow()
    {
        return $this->task(__FUNCTION__);
    }

    /**
     * Expose phpcs-beautify task.
     *
     * @return \Cheppers\Robo\Task\Phpcs\TaskPhpcsBeautify
     *   The task handler.
     */
    protected function taskPhpcsBeautify()
    {
        return $this->task(__FUNCTION__);
    }
}
