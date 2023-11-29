<?php

declare(strict_types = 1);

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

        $i->runRoboTask(
            $id,
            PhpcsRoboFile::class,
            $roboTaskName
        );

        $stdOutput = $i->getRoboTaskStdOutput($id);
        $stdError = $i->getRoboTaskStdError($id);
        $exitCode = $i->getRoboTaskExitCode($id);

        $reports = [
            'native.full',
            'extra.verbose',
            'extra.summary',
        ];
        foreach ($reports as $report) {
            $i->assertStringContainsString(
                file_get_contents("{$this->expectedDir}/01.$report.txt"),
                $stdOutput,
                "StdOutput contains the $report report"
            );
        }
        $i->haveAValidCheckstyleReport('actual/01.native.checkstyle.xml');

        $i->assertStringContainsString(
            'PHP Code Sniffer found some errors :-(',
            $stdError,
            'StdError contains a general message'
        );

        $i->assertSame(
            2,
            $exitCode,
            'Robo task exitCode',
        );
    }

    public function lintFilesNonExists(AcceptanceTester $i)
    {
        $id = __METHOD__;
        $roboTaskName = 'lint-files:non-exists';
        $i->wantTo("Run Robo task '<comment>$roboTaskName</comment>'.");

        $i->runRoboTask(
            $id,
            PhpcsRoboFile::class,
            $roboTaskName,
            'non-exists.php'
        );

        $stdOutput = $i->getRoboTaskStdOutput($id);
        $stdError = $i->getRoboTaskStdError($id);
        $exitCode = $i->getRoboTaskExitCode($id);

        $i->assertStringContainsString(
            'ERROR: The file "fixtures/non-exists.php" does not exist.',
            $stdError,
            'StdError contains a general message'
        );

        $i->assertSame(
            3,
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
        static $callCounter = 1;

        $id = __METHOD__ . ':' . $callCounter++;

        $command = "$roboTaskName " . implode(' ', $argsAndOptions);
        $i->wantTo("Run Robo task '<comment>$command</comment>'.");

        $i->runRoboTask(
            $id,
            PhpcsRoboFile::class,
            $roboTaskName,
            ...$argsAndOptions
        );

        $i->assertSame(2, $i->getRoboTaskExitCode($id));
        $i->haveAFileLikeThis('02-03.extra.checkstyle.xml');
        $i->haveAFileLikeThis('02-03.extra.summary.txt');
        $i->haveAFileLikeThis('02-03.extra.verbose.txt');
        $i->assertStringContainsString('PHP Code Sniffer found some errors :-(', $i->getRoboTaskStdError($id));
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
                ' [PHP_CodeSniffer - parse XML] XML file not found in directory: "vfs://RunRoboTasksCest.parseXml"',
                ' [' . PhpcsParseXml::class . ']  XML file not found in directory: "vfs://RunRoboTasksCest.parseXml" ',
                ' [' . PhpcsParseXml::class . ']  Exit code 1 ',
                '',
            ]),
            'exitCode' => 1,
        ];

        $i->assertSame($expected['stdOutput'], $stdOutput, 'stdOutput');
        $i->assertSame($expected['stdError'], $stdError, 'stdError');
        $i->assertSame($expected['exitCode'], $exitCode, 'exitCode');
    }
}
