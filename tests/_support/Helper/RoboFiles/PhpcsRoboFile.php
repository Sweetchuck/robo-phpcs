<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Phpcs\Test\Helper\RoboFiles;

use League\Container\Container as LeagueContainer;
use League\Container\ContainerAwareInterface;
use Psr\Container\ContainerInterface;
use Robo\Contract\TaskInterface;
use Robo\Tasks;
use Sweetchuck\LintReport\Reporter\BaseReporter;
use Sweetchuck\LintReport\Reporter\CheckstyleReporter;
use Sweetchuck\LintReport\Reporter\SummaryReporter;
use Sweetchuck\LintReport\Reporter\VerboseReporter;
use Sweetchuck\Robo\Phpcs\PhpcsTaskLoader;

class PhpcsRoboFile extends Tasks
{
    use PhpcsTaskLoader;

    /**
     * {@inheritdoc}
     */
    protected function output()
    {
        return $this->getContainer()->get('output');
    }

    /**
     * @return $this
     */
    public function setContainer(ContainerInterface $container): ContainerAwareInterface
    {
        $this->container = $container;

        foreach (BaseReporter::getServices() as $name => $class) {
            if ($this->container instanceof LeagueContainer) {
                $this->container->addShared($name, $class);
            }
        }

        return $this;
    }

    /**
     * @command lint-files:all-in-one
     */
    public function lintFilesAllInOne(): TaskInterface
    {
        $dataDir = $this->getDataDir();
        $reportsDir = 'actual';
        $fixturesDir = 'fixtures';

        $verboseFile = (new VerboseReporter())
            ->setFilePathStyle('relative')
            ->setDestination("$dataDir/$reportsDir/01.extra.verbose.txt");

        $summaryFile = (new SummaryReporter())
            ->setFilePathStyle('relative')
            ->setDestination("$dataDir/$reportsDir/01.extra.summary.txt");

        return $this
            ->taskPhpcsLintFiles()
            ->setWorkingDirectory($dataDir)
            ->setPhpcsExecutable($this->getPhpcsExecutable())
            ->setColors(false)
            ->setStandards(['PSR2'])
            ->setFiles(["$fixturesDir/psr2.invalid.01.php"])
            ->setReport('full')
            ->setReport('checkstyle', "$reportsDir/01.native.checkstyle.xml")
            ->addLintReporter('verbose:StdOutput', 'lintVerboseReporter')
            ->addLintReporter('verbose:file', $verboseFile)
            ->addLintReporter('summary:StdOutput', 'lintSummaryReporter')
            ->addLintReporter('summary:file', $summaryFile);
    }

    /**
     * @command lint-files:non-exists
     */
    public function lintFilesNonExists(string $nonExistsFile): TaskInterface
    {
        $dataDir = $this->getDataDir();
        $fixturesDir = 'fixtures';

        return $this
            ->taskPhpcsLintFiles()
            ->setPhpcsExecutable($this->getPhpcsExecutable())
            ->setWorkingDirectory($dataDir)
            ->setColors(false)
            ->setStandards(['PSR2'])
            ->setFailOn('warning')
            ->setFiles([
                "$fixturesDir/psr2.invalid.01.php",
                "$fixturesDir/$nonExistsFile",
            ])
            ->setReport('full');
    }

    /**
     * @command lint-input
     */
    public function lintInput(
        array $options = [
            'command-only' => false,
        ]
    ): TaskInterface {
        $dataDir = $this->getDataDir();
        $reportsDir = 'actual';
        $fixturesDir = 'fixtures';

        $verboseFile = (new VerboseReporter())
            ->setFilePathStyle('relative')
            ->setDestination("$dataDir/$reportsDir/02-03.extra.verbose.txt");

        $summaryFile = (new SummaryReporter())
            ->setFilePathStyle('relative')
            ->setDestination("$dataDir/$reportsDir/02-03.extra.summary.txt");

        $checkstyleFile = (new CheckstyleReporter())
            ->setFilePathStyle('relative')
            ->setDestination("$dataDir/$reportsDir/02-03.extra.checkstyle.xml");

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
            $files['psr2.invalid.02.php']['content'] = file_get_contents("$dataDir/$fixturesDir/psr2.invalid.02.php");
            $files['psr2.invalid.03.php']['content'] = file_get_contents("$dataDir/$fixturesDir/psr2.invalid.03.php");
        }

        return $this
            ->taskPhpcsLintInput()
            ->setWorkingDirectory($dataDir)
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

    protected function getDataDir(): string
    {
        return 'tests/_data';
    }

    protected function getPhpcsExecutable(): string
    {
        return '../../vendor/bin/phpcs';
    }
}
