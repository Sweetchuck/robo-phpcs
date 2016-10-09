<?php

$roboTaskName = 'lint:files-all-in-one';
$runMode = 'cli';
$expectedDir = codecept_data_dir('expected');

$i = new AcceptanceTester($scenario);
$i->wantTo("Run Robo task '<comment>$roboTaskName $runMode</comment>'.");
$i
    ->clearTheReportsDir()
    ->runRoboTask($roboTaskName, [$runMode])
    ->expectTheExitCodeToBe(2)
    ->seeThisTextInTheStdOutput(file_get_contents("$expectedDir/01.native.full.txt"))
    ->seeThisTextInTheStdOutput(file_get_contents("$expectedDir/01.extra.verbose.txt"))
    ->seeThisTextInTheStdOutput(file_get_contents("$expectedDir/01.extra.summary.txt"))
    ->seeThisTextInTheStdError('PHP Code Sniffer found some errors :-(')
    ->haveAValidCheckstyleReport('actual/01.native.checkstyle.xml');
