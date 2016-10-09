<?php

use \PHPUnit_Framework_Assert as Assert;

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
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    /**
     * @return $this
     */
    public function clearTheReportsDir()
    {
        $reportsDir = codecept_data_dir('actual');
        if (is_dir($reportsDir)) {
            $finder = new \Symfony\Component\Finder\Finder();
            $finder->in($reportsDir);
            foreach ($finder->files() as $file) {
                unlink($file->getPathname());
            }
        }

        return $this;
    }

    /**
     * @param string $taskName
     *
     * @return $this
     */
    public function runRoboTask($taskName, array $args = [], array $options = [])
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
     * @param string $fileName
     *
     * @return $this
     */
    public function haveAFileLikeThis($fileName) {
        $expectedDir = codecept_data_dir('expected');
        $actualDir = codecept_data_dir('actual');

        Assert::assertContains(
            file_get_contents("$expectedDir/$fileName"),
            file_get_contents("$actualDir/$fileName")
        );

        return $this;
    }

    public function haveAValidCheckstyleReport($fileName)
    {
        $fileName = "tests/_data/$fileName";
        $doc = new \DOMDocument();
        $doc->loadXML(file_get_contents($fileName));
        $xpath = new DOMXPath($doc);
        $rootElement = $xpath->query('/checkstyle');
        Assert::assertEquals(1, $rootElement->length, 'Root element of the Checkstyle XML is exists.');

        return $this;
    }

    /**
     * @param string $expected
     *
     * @return $this
     */
    public function seeThisTextInTheStdOutput($expected)
    {
        Assert::assertContains($expected, $this->getStdOutput());

        return $this;
    }

    /**
     * @param string $expected
     *
     * @return $this
     */
    public function seeThisTextInTheStdError($expected)
    {
        Assert::assertContains($expected, $this->getStdError());

        return $this;
    }

    /**
     * @param int $expected
     *
     * @return $this
     */
    public function expectTheExitCodeToBe($expected)
    {
        Assert::assertEquals($expected, $this->getExitCode());

        return $this;
    }
}
