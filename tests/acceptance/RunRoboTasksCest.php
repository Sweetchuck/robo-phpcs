<?php

namespace Sweetchuck\Robo\Phpcs\Tests\Acceptance;

use org\bovigo\vfs\vfsStream;
use Sweetchuck\Robo\Phpcs\Task\PhpcsParseXml;
use Sweetchuck\Robo\Phpcs\Test\AcceptanceTester;
use Sweetchuck\Robo\Phpcs\Test\Helper\RoboFiles\PhpcsRoboFile;

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

    public function _before(AcceptanceTester $i)
    {
        $i->clearTheReportsDir();
    }

    public function lintFilesAllInOneTask(AcceptanceTester $i)
    {
        $id = __METHOD__;
        $roboTaskName = 'lint-files:all-in-one';
        $i->wantTo("Run Robo task '<comment>$roboTaskName</comment>'.");

        $cwd = getcwd();
        chdir(codecept_data_dir());
        $i->runRoboTask(
            $id,
            PhpcsRoboFile::class,
            $roboTaskName
        );
        chdir($cwd);

        $stdOutput = $i->getRoboTaskStdOutput($id);
        $stdError = $i->getRoboTaskStdError($id);
        $exitCode = $i->getRoboTaskExitCode($id);

        $reports = [
            'native.full',
            'extra.verbose',
            'extra.summary',
        ];
        foreach ($reports as $report) {
            $i->assertContains(
                file_get_contents("{$this->expectedDir}/01.$report.txt"),
                $stdOutput,
                "StdOutput contains the $report report"
            );
        }
        $i->haveAValidCheckstyleReport('actual/01.native.checkstyle.xml');

        $i->assertContains(
            'PHP Code Sniffer found some errors :-(',
            $stdError,
            'StdError contains a general message'
        );

        $i->assertEquals(
            2,
            $exitCode,
            'Robo task exitCode'
        );
    }

    public function lintInputTaskCommandOnlyFalse(AcceptanceTester $i)
    {
        $this->lintInput($i, 'lint-input');
    }

    public function lintInputTaskCommandOnlyTrue(AcceptanceTester $i)
    {
        $this->lintInput($i, 'lint-input', ['--command-only']);
    }

    protected function lintInput(AcceptanceTester $i, string $roboTaskName, array $argsAndOptions = [])
    {
        // @todo https://github.com/Sweetchuck/robo-phpcs/issues/6
        if (getenv('TRAVIS_OS_NAME') === 'osx') {
            $i->wantTo("Skip the '$roboTaskName' task, because it does not work on OSX");

            return;
        }

        static $callCounter = 1;

        $id = __METHOD__ . ':' . $callCounter++;

        $command = "$roboTaskName " . implode(' ', $argsAndOptions);
        $i->wantTo("Run Robo task '<comment>$command</comment>'.");

        $cwd = getcwd();
        chdir(codecept_data_dir());
        $i->runRoboTask(
            $id,
            PhpcsRoboFile::class,
            $roboTaskName,
            ...$argsAndOptions
        );
        chdir($cwd);

        $i->assertEquals(2, $i->getRoboTaskExitCode($id));
        $i->haveAFileLikeThis('02-03.extra.checkstyle.xml');
        $i->haveAFileLikeThis('02-03.extra.summary.txt');
        $i->haveAFileLikeThis('02-03.extra.verbose.txt');
        $i->assertContains('PHP Code Sniffer found some errors :-(', $i->getRoboTaskStdError($id));
    }

    public function parseXml(AcceptanceTester $i)
    {
        $id = __FUNCTION__;
        $vfs = vfsStream::setup("RunRoboTasksCest.$id");

        $i->runRoboTask(
            $id,
            PhpcsRoboFile::class,
            'parse-xml',
            $vfs->url()
        );

        $stdOutput = $i->getRoboTaskStdOutput($id);
        $stdError = $i->getRoboTaskStdError($id);
        $exitCode = $i->getRoboTaskExitCode($id);

        $expected = [
            'stdOutput' => '',
            'stdError' => implode("\n", [
                ' [PHP_CodeSniffer parse XML] XML file not found in directory: "vfs://RunRoboTasksCest.parseXml"',
                ' [' . PhpcsParseXml::class . ']  XML file not found in directory: "vfs://RunRoboTasksCest.parseXml" ',
                ' [' . PhpcsParseXml::class . ']  Exit code 1 ',
                ' [error]   ',
                '',
            ]),
            'exitCode' => 1,
        ];

        $i->assertEquals($expected['stdOutput'], $stdOutput, 'stdOutput');
        $i->assertEquals($expected['stdError'], $stdError, 'stdError');
        $i->assertEquals($expected['exitCode'], $exitCode, 'exitCode');
    }
}
