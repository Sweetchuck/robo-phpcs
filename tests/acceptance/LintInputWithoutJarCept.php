<?php

$roboTaskName = 'lint:input-with-jar';
$expectedDir = codecept_data_dir('expected');

$i = new AcceptanceTester($scenario);
$i->wantTo("Run Robo task '<comment>$roboTaskName</comment>'.");
$i
    ->clearTheReportsDir()
    ->runRoboTask($roboTaskName)
    ->expectTheExitCodeToBe(2)
    ->haveAFileLikeThis('02-03.extra.checkstyle.xml')
    ->haveAFileLikeThis('02-03.extra.summary.txt')
    ->haveAFileLikeThis('02-03.extra.verbose.txt')
    ->seeThisTextInTheStdError('PHP Code Sniffer found some errors :-(');
