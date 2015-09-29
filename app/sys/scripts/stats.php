<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

if (!defined('DP_ROOT')) {
    exit('No access');
}

require DP_ROOT.'/sys/load_config.php';
dp_load_config();

#------------------------------
# Verify auth
#------------------------------

if (!defined('DP_APC_STATS_INFO_AUTH')) {
    echo 'DP_APC_STATS_INFO_AUTH_UNDEFINED';
    exit(1);
}

if (!isset($_GET['auth']) || $_GET['auth'] != DP_APC_STATS_INFO_AUTH) {
    echo 'DP_APC_STATS_INFO_AUTH_INVALID';
    exit(1);
}

#------------------------------
# Print stats
#------------------------------

$data = array();

$val = @apc_fetch(DP_APC_STATS_KEY.'.query_count');
if (!$val) {
    $data['query_count.total']    = 0;
    $data['query_count.hour_avg'] = 0;
} else {
    $total = 0;
    foreach ($val as $hour => $count) {
        $total += $count;
        $data['query_count.hour.'.$hour] = $count;
    }

    if ($val) {
        $avg = ceil($total / count($val));
    } else {
        $avg = 0;
    }

    $data['query_count.total']    = $total;
    $data['query_count.hour_avg'] = $avg;
}

header('Content-Type: application/json');
echo json_encode($data);
