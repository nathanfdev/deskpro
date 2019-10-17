<?php

function dp_dev_get_php_bin()
{
    $envPhp = getenv('DP_PHP_BIN');
    if ($envPhp) {
        return $envPhp;
    } else {
        return '/usr/bin/env php';
    }
}
