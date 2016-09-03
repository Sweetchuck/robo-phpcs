<?php

$dataDir = rtrim(codecept_data_dir(), '/');

$i = new AcceptanceTester($scenario);

$i->wantTo('Run TaskPhpcsLint Robo task and save the Checkstyle report to XML file.');
$i
    ->clearTheReportsDir()
    ->runRoboTask('lint')
    ->theExitCodeShouldBe(1)
    ->seeThisTextInTheStdOutput('FILE: fixtures/psr2.invalid.php')
    ->haveAValidCheckstyleReport('reports/psr2.xml');
