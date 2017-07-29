<?php

namespace Sweetchuck\Robo\Phpcs\Test;

use Codeception\Actor;
use \PHPUnit_Framework_Assert as Assert;
use Sweetchuck\Robo\Phpcs\Test\_generated\AcceptanceTesterActions;
use Symfony\Component\Finder\Finder;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends Actor
{
    use AcceptanceTesterActions;

    /**
     * @return $this
     */
    public function clearTheReportsDir()
    {
        $reportsDir = codecept_data_dir('actual');
        if (is_dir($reportsDir)) {
            $finder = (new Finder())
                ->in($reportsDir)
                ->files();
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            foreach ($finder as $file) {
                unlink($file->getPathname());
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function runRoboTask(string $taskName, array $args = [], array $options = [])
    {
        $cmdPattern = 'cd %s && ../../bin/robo %s';
        $cmdArgs = [
            escapeshellarg(codecept_data_dir()),
            escapeshellarg($taskName),
        ];

        foreach ($options as $option => $value) {
            $cmdPattern .= " --$option";
            if ($value !== null) {
                $cmdPattern .= '=%s';
                $cmdArgs[] = escapeshellarg($value);
            }
        }

        $cmdPattern .= str_repeat(' %s', count($args));
        foreach ($args as $arg) {
            $cmdArgs[] = escapeshellarg($arg);
        }

        $this->runShellCommand(vsprintf($cmdPattern, $cmdArgs));

        return $this;
    }

    /**
     * @return $this
     */
    public function haveAFileLikeThis(string $fileName)
    {
        $expectedDir = codecept_data_dir('expected');
        $actualDir = codecept_data_dir('actual');

        Assert::assertContains(
            file_get_contents("$expectedDir/$fileName"),
            file_get_contents("$actualDir/$fileName")
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function haveAValidCheckstyleReport(string $fileName)
    {
        $fileName = "tests/_data/$fileName";
        $doc = new \DOMDocument();
        $doc->loadXML(file_get_contents($fileName));
        $xpath = new \DOMXPath($doc);
        $rootElement = $xpath->query('/checkstyle');
        Assert::assertEquals(1, $rootElement->length, 'Root element of the Checkstyle XML is exists.');

        return $this;
    }

    /**
     * @return $this
     */
    public function seeThisTextInTheStdOutput(string $expected)
    {
        Assert::assertContains($expected, $this->getStdOutput());

        return $this;
    }

    /**
     * @return $this
     */
    public function seeThisTextInTheStdError(string $expected)
    {
        Assert::assertContains($expected, $this->getStdError());

        return $this;
    }

    /**
     * @return $this
     */
    public function expectTheExitCodeToBe(int $expected)
    {
        Assert::assertEquals($expected, $this->getExitCode());

        return $this;
    }
}
