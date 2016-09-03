<?php

/**
 * Class RoboFile.
 */
// @codingStandardsIgnoreStart
class RoboFile extends \Robo\Tasks
{
    // @codingStandardsIgnoreEnd
    use \Cheppers\Robo\Phpcs\Task\LoadTasks;

    public function lint()
    {
        return $this->taskPhpcsLint()
            ->standard('PSR2')
            ->report('full')
            ->report('checkstyle', 'reports/psr2.xml')
            ->files(['fixtures/']);
    }
}
