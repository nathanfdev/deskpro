<?php

$SETTINGS = [];
$SETTINGS['disable_portal_http_cache'] = true;
$SETTINGS['disable_url_corrections']   = false;
$SETTINGS['disable_outgoing_email']    = true;

// raw assets in agent interface
$SETTINGS['raw_assets'] = ['all'];

######################################################
# Basic settings for logging and dupe system         #
######################################################
# You can disable or enable loggin here and          #
# where you want to write logs + which modes would   #
# be logged or matched agains dupes                  #
######################################################
$SETTINGS['api_log.enabled'] = false;
$SETTINGS['api_log.modes'] = ['key'];
$SETTINGS['api_log.writer.type'] = 'db';
$SETTINGS['api_log.writer.file.serializer.type'] = 'human_readable';
$SETTINGS['api_log.writer.file'] = [
    'log_max_size'  => 5 * 1024 * 1024,
    'log_max_files' => 5,
    'log_name'      => 'api_log.log',
];

$SETTINGS['api_log.dupe.modes'] = ['key'];
$SETTINGS['audit_log.storage'] = "db";

$SETTINGS['bugsnag'] = [
    'api_key'    => '',
    'enable_php' => false,
    'enable_js'  => false,
    'app_version' => null, //if not set, or null it will be taken as DP_BUILD_NUM
    'metadata' => [], //anything you want
];