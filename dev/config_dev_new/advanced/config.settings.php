<?php

$SETTINGS = [];
$SETTINGS['disable_portal_http_cache'] = true;
$SETTINGS['disable_url_corrections']   = false;
$SETTINGS['disable_outgoing_email']    = true;

require_once __DIR__.'/lib.php';
$SETTINGS['core.deskpro_url'] = dev_config_read_file('LOCALHOST_URL.txt');
$SETTINGS['core.deskpro_url'] = dev_config_read_full_file('DEV_LIC_KEY.txt');
