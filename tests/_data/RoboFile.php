<?php

use Cheppers\AssetJar\AssetJar;
use Cheppers\LintReport\Reporter\BaseReporter;
use Cheppers\LintReport\Reporter\CheckstyleReporter;
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
     * @return $this
     */
    public function lintFilesAllInOne()
    {
        $reportsDir = 'actual';

        $verboseFile = (new VerboseReporter())
            ->setFilePathStyle('relative')
            ->setDestination("$reportsDir/01.extra.verbose.txt");

        $summaryFile = (new SummaryReporter())
            ->setFilePathStyle('relative')
            ->setDestination("$reportsDir/01.extra.summary.txt");

        return $this->taskPhpcsLintFiles()
            ->setColors(false)
            ->setStandard('PSR2')
            ->setFiles(['fixtures/psr2.invalid.01.php'])
            ->setReport('full')
            ->setReport('checkstyle', "$reportsDir/01.native.checkstyle.xml")
            ->addLintReporter('verbose:StdOutput', 'lintVerboseReporter')
            ->addLintReporter('verbose:file', $verboseFile)
            ->addLintReporter('summary:StdOutput', 'lintSummaryReporter')
            ->addLintReporter('summary:file', $summaryFile);
    }

    public function lintInputWithoutJar()
    {
        $fixturesDir = 'fixtures';
        $reportsDir = 'actual';

        $verboseFile = (new VerboseReporter())
            ->setFilePathStyle('relative')
            ->setDestination("$reportsDir/02-03.extra.verbose.txt");

        $summaryFile = (new SummaryReporter())
            ->setFilePathStyle('relative')
            ->setDestination("$reportsDir/02-03.extra.summary.txt");

        $checkstyleFile = (new CheckstyleReporter())
            ->setFilePathStyle('relative')
            ->setDestination("$reportsDir/02-03.extra.checkstyle.xml");

        return $this->taskPhpcsLintInput()
            ->setStandard('PSR2')
            ->setFiles([
                'psr2.invalid.02.php' => [
                    'fileName' => 'psr2.invalid.02.php',
                    'content' => file_get_contents("$fixturesDir/psr2.invalid.02.php"),
                ],
                'psr2.invalid.03.php' => [
                    'fileName' => 'psr2.invalid.02.php',
                    'content' => file_get_contents("$fixturesDir/psr2.invalid.03.php"),
                ],
            ])
            ->addLintReporter('verbose:StdOutput', 'lintVerboseReporter')
            ->addLintReporter('verbose:file', $verboseFile)
            ->addLintReporter('summary:StdOutput', 'lintSummaryReporter')
            ->addLintReporter('summary:file', $summaryFile)
            ->addLintReporter('checkstyle:file', $checkstyleFile);
    }

    public function lintInputWithJar()
    {
        $task = $this->lintInputWithoutJar();
        $assetJar = new AssetJar([
            'l1' => [
                'l2' => $task->getFiles(),
            ],
        ]);

        return $task
            ->setFiles([])
            ->setAssetJar($assetJar)
            ->setAssetJarMap('files', ['l1', 'l2']);
    }
}
