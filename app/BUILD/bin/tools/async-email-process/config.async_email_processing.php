<?php

$AEP_CONFIG = ['process' => [], 'collect' => []];

$AEP_CONFIG['process']['max_time']      = 600;
$AEP_CONFIG['process']['max_processes'] = 8;
$AEP_CONFIG['process']['redis_key']     = 'dp_incoming_email';
$AEP_CONFIG['process']['redis_params']  = [
    'scheme'             => 'tcp',
    'host'               => '127.0.0.1',
    'port'               => 32768,
    'read_write_timeout' => 20,
];

$AEP_CONFIG['collect']['max_time']         = 600;
$AEP_CONFIG['collect']['max_processes']    = 8;
$AEP_CONFIG['collect']['connect_interval'] = 10;
