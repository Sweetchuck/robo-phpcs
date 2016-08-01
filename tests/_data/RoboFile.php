<?php

// @codingStandardsIgnoreStart
use Symfony\Component\Process\Process;

/**
 * Class RoboFile.
 */
class RoboFile extends \Robo\Tasks
    // @codingStandardsIgnoreEnd
{
    use \Cheppers\Robo\Task\Phpcs\LoadTasks;

    /**
     * RoboFile constructor.
     */
    public function __construct()
    {
        $this->setContainer(\Robo\Robo::getContainer());

        /** @var \League\Container\Container $c */
        $c = $this->getContainer();
        $c
            ->addServiceProvider(static::getPhpcsServiceProvider())
            ->addServiceProvider(\Robo\Task\Filesystem\loadTasks::getFilesystemServices());
    }

    public function lint()
    {
        return $this->taskPhpcsLint()
            ->standard('PSR2')
            ->report('full')
            ->report('checkstyle', 'reports/psr2.xml')
            ->files(['fixtures/']);
    }

}
