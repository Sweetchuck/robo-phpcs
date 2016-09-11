<?php

$roboTaskName = 'lint:full-std-output-and-checkstyle-file';
$runMode = 'native';

$i = new AcceptanceTester($scenario);
$i->wantTo("Run Robo task '<comment>$roboTaskName $runMode</comment>'.");
$i
    ->clearTheReportsDir()
    ->runRoboTask($roboTaskName, [$runMode])
    ->expectTheExitCodeToBe(1)
    ->seeThisTextInTheStdOutput('fixtures/psr2.invalid.php')
    ->seeThisTextInTheStdError('PHP Code Sniffer found some errors :-(')
    ->haveAValidCheckstyleReport('reports/psr2.xml');
