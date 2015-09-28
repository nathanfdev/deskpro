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
/**
 * DeskPRO.
 *
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 */

/**
 * This handles writing the special chat_is_available.trigger file based.
 * It is used when multiple front-end web servers are in use. The normal
 * chat_ping_timeout cron job posts to this script so the local server has the proper trigger.
 *
 * Required GET params:
 * - auth: Must be the defined DP_CHATSTATUS_AUTH
 * - is_chat_available: Either 1 or 0
 */
require DP_ROOT.'/sys/load_config.php';
dp_load_config();

#------------------------------
# Verify auth
#------------------------------

if (!defined('DP_CHATSTATUS_AUTH')) {
    echo 'DP_CHATSTATUS_AUTH_UNDEFINED';
    exit(1);
}

if (!isset($_GET['auth']) || $_GET['auth'] != DP_CHATSTATUS_AUTH) {
    echo 'DP_CHATSTATUS_AUTH_INVALID';
    exit(1);
}

#------------------------------
# Write file
#------------------------------

$trigger_file = dp_get_data_dir().'/chat_is_available.trigger';
if (isset($_GET['is_chat_available']) && $_GET['is_chat_available']) {
    if (!file_put_contents($trigger_file, time())) {
        echo 'DP_CHATSTATUS_FAIL_AVAILABLE';
        exit;
    }
    @chmod($trigger_File, 0777);
    echo 'DP_CHATSTATUS_WROTE_AVAILABLE';
} else {
    if (file_exists($trigger_file) && !unlink($trigger_file)) {
        echo 'DP_CHATSTATUS_FAILED_UNAVAILABLE';
        exit;
    }

    echo 'DP_CHATSTATUS_WROTE_UNAVAILABLE';
}
