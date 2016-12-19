<?php

$LOGS_CONFIG = [];

$LOGS_CONFIG['log_level']           = 'debug';
$LOGS_CONFIG['log_level_threshold'] = 'error';

$LOGS_CONFIG['enable_ticket_log']     = false;
$LOGS_CONFIG['enable_usersource_log'] = false;

$LOGS_CONFIG['page_log'] = [
    'enabled'           => false,
    'url_pattern'       => [],
    'slow_query_time'   => false,
    'max_query_count'   => false,
    'slow_db_time'      => false,
    'slow_php_time'     => false,
    'slow_page_time'    => false,
    'tracked_query_log' => false,
    'track_query_ids'   => [],
    'track_query_regex' => [],
];
