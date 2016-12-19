<?php

$PATHS_CONFIG = [];
$PATHS_CONFIG['php_path']       = '';
$PATHS_CONFIG['mysqldump_path'] = '';
$PATHS_CONFIG['mysql_path']     = '';

// load raw (uncompiled) legacy assets in agent interface
$PATHS_CONFIG['raw_assets'] = ['all'];

$PATHS_CONFIG['asset_paths'] = [];

// Uncomment to enable build server
$PATHS_CONFIG['asset_paths']['app_assets'] = [
    'type'    => 'url',
    'value'   => 'http://localhost:9666/pub/build/'
];
