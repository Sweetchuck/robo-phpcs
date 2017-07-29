<?php

namespace Sweetchuck\Robo\Phpcs\Tests\Acceptance;

use \Sweetchuck\Robo\Phpcs\Test\AcceptanceTester;

class RunRoboTasksCest
{
    /**
     * @var string
     */
    protected $expectedDir = '';

    public function __construct()
    {
        $this->expectedDir = codecept_data_dir('expected');
    }

    // @codingStandardsIgnoreStart
    public function _before(AcceptanceTester $i)
    {
        // @codingStandardsIgnoreEnd
        $i->clearTheReportsDir();
    }

    public function lintFilesAllInOneTask(AcceptanceTester $i)
    {
        $roboTaskName = 'lint:files-all-in-one';
        $i->wantTo("Run Robo task '<comment>$roboTaskName</comment>'.");
        $i
            ->runRoboTask($roboTaskName)
            ->expectTheExitCodeToBe(2)
            ->seeThisTextInTheStdOutput(file_get_contents("{$this->expectedDir}/01.native.full.txt"))
            ->seeThisTextInTheStdOutput(file_get_contents("{$this->expectedDir}/01.extra.verbose.txt"))
            ->seeThisTextInTheStdOutput(file_get_contents("{$this->expectedDir}/01.extra.summary.txt"))
            ->seeThisTextInTheStdError('PHP Code Sniffer found some errors :-(')
            ->haveAValidCheckstyleReport('actual/01.native.checkstyle.xml');
    }

    public function lintInputWithoutJarTaskCommandOnlyFalse(AcceptanceTester $i)
    {
        $this->lintInput($i, 'lint:input-without-jar');
    }

    public function lintInputWithoutJarTaskCommandOnlyTrue(AcceptanceTester $i)
    {
        $this->lintInput($i, 'lint:input-without-jar', [], ['command-only' => null]);
    }

    public function lintInputWithJarTaskCommandOnlyFalse(AcceptanceTester $i)
    {
        $this->lintInput($i, 'lint:input-with-jar');
    }

    public function lintInputWithJarTaskCommandOnlyTrue(AcceptanceTester $i)
    {
        $this->lintInput($i, 'lint:input-with-jar', [], ['command-only' => null]);
    }

    /**
     * @param AcceptanceTester $i
     * @param string $roboTaskName
     * @param array $args
     * @param array $options
     */
    protected function lintInput(AcceptanceTester $i, $roboTaskName, array $args = [], array $options = [])
    {
        // @todo https://github.com/Sweetchuck/robo-phpcs/issues/6
        if (getenv('TRAVIS_OS_NAME') === 'osx') {
            $i->wantTo("Skip the '$roboTaskName' task, because it does not work on OSX");

            return;
        }

        $cmdPattern = '%s';
        $cmdArgs = [
            escapeshellarg($roboTaskName),
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

        $command = vsprintf($cmdPattern, $cmdArgs);

        $i->wantTo("Run Robo task '<comment>$command</comment>'.");
        $i
            ->runRoboTask($roboTaskName)
            ->expectTheExitCodeToBe(2)
            ->haveAFileLikeThis('02-03.extra.checkstyle.xml')
            ->haveAFileLikeThis('02-03.extra.summary.txt')
            ->haveAFileLikeThis('02-03.extra.verbose.txt')
            ->seeThisTextInTheStdError('PHP Code Sniffer found some errors :-(');
    }
}
