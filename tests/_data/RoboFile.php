<?php

/**
 * Class RoboFile.
 */
// @codingStandardsIgnoreStart
class RoboFile extends \Robo\Tasks
{
    // @codingStandardsIgnoreEnd
    use \Cheppers\Robo\Phpcs\Task\LoadTasks;

    /**
     * @param string $runMode
     *   Allowed values are: cli, native.
     *
     * @return $this
     */
    public function lintFullStdOutputAndCheckstyleFile($runMode)
    {
        return $this->taskPhpcsLint()
            ->setOutput($this->getOutput())
            ->runMode($runMode)
            ->standard('PSR2')
            ->report('full')
            ->report('checkstyle', 'reports/psr2.xml')
            ->files(['fixtures/']);
    }
}
