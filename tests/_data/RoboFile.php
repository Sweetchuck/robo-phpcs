<?php

use Cheppers\LintReport\Reporter\BaseReporter;
use Cheppers\LintReport\Reporter\SummaryReporter;
use Cheppers\LintReport\Reporter\VerboseReporter;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerInterface;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\OutputAwareInterface;

/**
 * Class RoboFile.
 */
// @codingStandardsIgnoreStart
class RoboFile extends \Robo\Tasks implements ContainerAwareInterface, ConfigAwareInterface
{
    // @codingStandardsIgnoreEnd
    use \Cheppers\Robo\Phpcs\Task\LoadTasks;
    use \League\Container\ContainerAwareTrait;
    use \Robo\Common\ConfigAwareTrait;

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        BaseReporter::lintReportConfigureContainer($this->container);

        return $this;
    }

    /**
     * @param string $runMode
     *   Allowed values are: cli, native.
     *
     * @return $this
     */
    public function lintAllInOne($runMode)
    {
        $reportsDir = 'actual';
        $verboseFile = new VerboseReporter();
        $verboseFile
            ->setFilePathStyle('relative')
            ->setDestination("$reportsDir/extra.verbose.txt");

        $summaryFile = new SummaryReporter();
        $summaryFile
            ->setFilePathStyle('relative')
            ->setDestination("$reportsDir/extra.summary.txt");

        return $this->taskPhpcsLintFiles()
            ->setRunMode($runMode)
            ->setColors(false)
            ->setStandard('PSR2')
            ->setFiles(['fixtures/'])
            ->setReport('full')
            ->setReport('checkstyle', "$reportsDir/native.checkstyle.xml")
            ->addLintReporter('verbose:StdOutput', 'lintVerboseReporter')
            ->addLintReporter('verbose:file', $verboseFile)
            ->addLintReporter('summary:StdOutput', 'lintSummaryReporter')
            ->addLintReporter('summary:file', $summaryFile);
    }

    public function lintInput()
    {
        return $this->taskPhpcsLintInput()
            ->setRunMode('native')
            ->setReport('full')
            ->setStandard('PSR2')
            ->setFiles([
                '.',
            ]);
    }

    public function lintNotExists($runMode)
    {
        return $this
            ->validateRunMode($runMode)
            ->taskPhpcsLintFiles()
            ->setRunMode($runMode)
            ->setReport('full')
            ->setStandard('PSR2')
            ->setFiles([
                'sadsad',
            ]);
    }

    /**
     * @param string $runMode
     *
     * @return $this
     */
    protected function validateRunMode($runMode)
    {
        if (!in_array($runMode, ['cli', 'native'])) {
            throw new \InvalidArgumentException("Run mode is invalid: '$runMode'");
        }

        return $this;
    }
}
