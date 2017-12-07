<?php

use Robo\Contract\TaskInterface;
use Sweetchuck\LintReport\Reporter\BaseReporter;
use Sweetchuck\LintReport\Reporter\CheckstyleReporter;
use Sweetchuck\LintReport\Reporter\SummaryReporter;
use Sweetchuck\LintReport\Reporter\VerboseReporter;
use League\Container\ContainerInterface;
use Sweetchuck\Robo\Phpcs\PhpcsTaskLoader;
use Webmozart\PathUtil\Path;

// @codingStandardsIgnoreStart
class PhpcsRoboFile extends \Robo\Tasks
{
    // @codingStandardsIgnoreEnd
    use PhpcsTaskLoader;

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

        return $this
            ->taskPhpcsLintFiles()
            ->setPhpcsExecutable($this->getPhpcsExecutable())
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
    public function lintInput(
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

        return $this
            ->taskPhpcsLintInput()
            ->setPhpcsExecutable($this->getPhpcsExecutable())
            ->setStandards(['PSR2'])
            ->setFiles($files)
            ->addLintReporter('verbose:StdOutput', 'lintVerboseReporter')
            ->addLintReporter('verbose:file', $verboseFile)
            ->addLintReporter('summary:StdOutput', 'lintSummaryReporter')
            ->addLintReporter('summary:file', $summaryFile)
            ->addLintReporter('checkstyle:file', $checkstyleFile);
    }

    /**
     * @command parse-xml
     */
    public function parseXml(
        string $dir,
        array $options = [
            'skipIfXmlFileNotExists' => false,
        ]
    ): TaskInterface {
        $localOptions = [
            'workingDirectory' => $dir,
            'failOnXmlFileNotExists' => !$options['skipIfXmlFileNotExists'],
        ];

        return $this->taskPhpcsParseXml($localOptions);
    }

    protected function getPhpcsExecutable(): string
    {
        $phpcsExecutable = Path::join(
            __DIR__,
            '..',
            '..',
            'bin',
            'phpcs'
        );

        return Path::makeRelative($phpcsExecutable, getcwd());
    }
}
