<?php

/**
 * This command dumps config as JSON to stdout.
 */

namespace DpSys\Bin\Util;

use DpRun\DpEnv;

function dump_config(DpEnv $dpEnv)
{
    $all = [];

    foreach ([
        'database',
        'database_advanced',
        'settings',
        'upgrader',
        'logs',
        'env',
        'paths',
    ] as $key) {
        $v = $dpEnv->getConfig($key);
        if (!empty($v)) {
            $all[$key] = $v;
        }
    }

    $flat       = in_array('--flat', $_SERVER['argv']);
    $flatString = in_array('--flat-string', $_SERVER['argv']);

    if ($flat || $flatString) {
        $flatten = function (array $arr, $keyParts = []) use (&$flatten, $flatString) {
            $new = [];

            foreach ($arr as $k => $v) {
                $keyParts[] = $k;
                if (is_array($v)) {
                    $sub = $flatten($v, $keyParts);
                    $new = array_merge($new, $sub);
                } else {
                    if (is_scalar($v)) {
                        $new[implode('.', $keyParts)] = $flatString ? (string) $v : $v;
                    }
                }
                array_pop($keyParts);
            }

            return $new;
        };

        $all = $flatten($all);
    }

    echo json_encode($all, \JSON_PRETTY_PRINT);
}

if (!empty($DP_ENV)) {
    dump_config($DP_ENV);
} else {
    echo 'Unknown env';
    exit(1);
}
