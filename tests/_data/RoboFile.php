<?php

use Sweetchuck\AssetJar\AssetJar;
use Sweetchuck\LintReport\Reporter\BaseReporter;
use Sweetchuck\LintReport\Reporter\CheckstyleReporter;
use Sweetchuck\LintReport\Reporter\SummaryReporter;
use Sweetchuck\LintReport\Reporter\VerboseReporter;
use League\Container\ContainerInterface;

// @codingStandardsIgnoreStart
class RoboFile extends \Robo\Tasks
{
    // @codingStandardsIgnoreEnd
    use \Sweetchuck\Robo\Phpcs\PhpcsTaskLoader;

    /**
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        BaseReporter::lintReportConfigureContainer($this->container);

        return $this;
    }

    /**
     * @return \Sweetchuck\Robo\Phpcs\Task\PhpcsLintFiles|\Robo\Collection\CollectionBuilder
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
            ->setStandards(['PSR2'])
            ->setFiles(['fixtures/psr2.invalid.01.php'])
            ->setReport('full')
            ->setReport('checkstyle', "$reportsDir/01.native.checkstyle.xml")
            ->addLintReporter('verbose:StdOutput', 'lintVerboseReporter')
            ->addLintReporter('verbose:file', $verboseFile)
            ->addLintReporter('summary:StdOutput', 'lintSummaryReporter')
            ->addLintReporter('summary:file', $summaryFile);
    }

    /**
     * @return \Sweetchuck\Robo\Phpcs\Task\PhpcsLintInput|\Robo\Collection\CollectionBuilder
     */
    public function lintInputWithoutJar(
        array $options = [
            'command-only' => false,
        ]
    ) {
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

        $files = [
            'psr2.invalid.02.php' => [
                'fileName' => 'psr2.invalid.02.php',
                'command' => "cat $fixturesDir/psr2.invalid.02.php",
                'content' => null,
            ],
            'psr2.invalid.03.php' => [
                'fileName' => 'psr2.invalid.03.php',
                'command' => "cat $fixturesDir/psr2.invalid.03.php",
                'content' => null,
            ],
        ];

        if (!$options['command-only']) {
            $files['psr2.invalid.02.php']['content'] = file_get_contents("$fixturesDir/psr2.invalid.02.php");
            $files['psr2.invalid.03.php']['content'] = file_get_contents("$fixturesDir/psr2.invalid.03.php");
        }

        return $this->taskPhpcsLintInput()
            ->setStandards(['PSR2'])
            ->setFiles($files)
            ->addLintReporter('verbose:StdOutput', 'lintVerboseReporter')
            ->addLintReporter('verbose:file', $verboseFile)
            ->addLintReporter('summary:StdOutput', 'lintSummaryReporter')
            ->addLintReporter('summary:file', $summaryFile)
            ->addLintReporter('checkstyle:file', $checkstyleFile);
    }

    /**
     * @return \Sweetchuck\Robo\Phpcs\Task\PhpcsLintInput|\Robo\Collection\CollectionBuilder
     */
    public function lintInputWithJar(
        $options = [
            'command-only' => false,
        ]
    ) {
        $task = $this->lintInputWithoutJar($options);
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
