#!/usr/bin/env php
<?php
define('DP_ROOT', realpath(__DIR__ . '/../../'));
require DP_ROOT . '/bin/build/inc.php';
require DP_ROOT.'/sys/system.php';

$kernel = new \DeskPRO\Kernel\CliKernel('dev', true);

$_SERVER['argv'] = array('x', 'dpdev:assetic', '-r', 'ALL');

$application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
$application->run();
