<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
