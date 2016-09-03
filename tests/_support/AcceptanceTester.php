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
        $reportsDir = 'tests/_data/reports';
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
    public function runRoboTask($taskName)
    {
        $cmd = sprintf(
            'cd tests/_data && ../../bin/robo %s',
            escapeshellarg($taskName)
        );

        $this->runShellCommand($cmd);

        return $this;
    }

    public function haveAValidCheckstyleReport($fileName)
    {
        $fileName = "tests/_data/$fileName";
        $doc = new \DOMDocument();
        $doc->loadXML(file_get_contents($fileName));
        $errors = $doc->getElementsByTagName('error');
        Assert::assertGreaterThan(0, $errors->length);

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
     * @param int $expected
     *
     * @return $this
     */
    public function theExitCodeShouldBe($expected)
    {
        Assert::assertEquals($expected, $this->getExitCode());

        return $this;
    }
}
