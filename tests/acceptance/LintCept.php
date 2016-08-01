<?php

$dataDir = rtrim(codecept_data_dir(), '/');

$i = new AcceptanceTester($scenario);

$i->wantTo('Run TaskPhpcsLint Robo task and save the Checkstyle report to XML file.');

$cmd = sprintf(
    '[[ ! -d %s ]] || rm -rf %s',
    escapeshellarg("$dataDir/reports"),
    escapeshellarg("$dataDir/reports")
);
$i->runShellCommand($cmd);

$cmd = sprintf('bin/robo --load-from %s lint', escapeshellarg($dataDir));
$i->runShellCommand($cmd, false);
$i->seeInShellOutput('FILE: fixtures/psr2.invalid.php');
$i->runShellCommand(sprintf('test -s %s', escapeshellarg("$dataDir/reports/psr2.xml")));
