<?php

$roboTaskName = 'lint:all-in-one';
$runMode = 'cli';
$expectedDir = codecept_data_dir('expected');

$i = new AcceptanceTester($scenario);
$i->wantTo("Run Robo task '<comment>$roboTaskName $runMode</comment>'.");
$i
    ->clearTheReportsDir()
    ->runRoboTask($roboTaskName, [$runMode])
    ->expectTheExitCodeToBe(2)
    ->seeThisTextInTheStdOutput(file_get_contents("$expectedDir/native.full.txt"))
    ->seeThisTextInTheStdOutput(file_get_contents("$expectedDir/extra.verbose.txt"))
    ->seeThisTextInTheStdOutput(file_get_contents("$expectedDir/extra.summary.txt"))
    ->seeThisTextInTheStdError('PHP Code Sniffer found some errors :-(')
    ->haveAValidCheckstyleReport('actual/native.checkstyle.xml');
