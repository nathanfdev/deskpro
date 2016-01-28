<?php

define('DP_ROOT', realpath(__DIR__.'/../'));

if (is_file(DP_ROOT.'/data/path.txt')) {
    define('DP_DATA_DIR', rtrim(trim(file_get_contents(DP_ROOT.'/data/path.txt'))), '/\\');
} else {
    define('DP_DATA_DIR', DP_ROOT.'/data');
}

if (file_exists(DP_DATA_DIR.'/hddata/active-build.txt')) {
    define('DP_ACTIVE_BUILD', trim(file_get_contents(DP_DATA_DIR.'/hddata/active-build.txt')));
}

