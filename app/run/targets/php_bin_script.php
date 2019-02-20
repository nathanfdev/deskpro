<?php

if (!defined('DP_RUN_BIN_SCRIPT')) {
    echo "DP_RUN_BIN_SCRIPT is not defined.\n";
    exit(1);
}

require __DIR__.'/../init_env.php';

$bin_path = $DP_ENV->getAppDir()
    . DIRECTORY_SEPARATOR . 'bin'
    . DIRECTORY_SEPARATOR . DP_RUN_BIN_SCRIPT;

if (!is_file($bin_path)) {
    echo DP_RUN_BIN_SCRIPT . " is an invlaid script\n";
    exit(1);
}

require $bin_path;