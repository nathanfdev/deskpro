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

#------------------------------
# Load config
#------------------------------

require_once DP_ROOT.'/sys/load_config.php';
dp_load_config();

// If not authed, the only way we render phpinfo is if not installed or we have the auth
if (!isset($is_authed) || !$is_authed) {
    $is_authed = false;

    $auth = isset($_GET['auth']) ? $_GET['auth'] : false;
    if ($auth && dp_get_config('phpinfo_auth') && dp_get_config('phpinfo_auth') == $auth) {
        $is_authed = true;
    } elseif (!file_exists(dp_get_data_dir().'/is_installed.dat') || dp_get_config('debug.dev')) {
        $is_authed = true;
    }
}

if (!$is_authed) {
    die('Invalid auth code.');
}

#------------------------------
# Show PHP Info
#------------------------------

if (isset($_GET['cli'])) {
    if (!file_exists(dp_get_data_dir().'/cli-phpinfo.html')) {
        die('CLI phpinfo has not been generated yet');
    }

    $phpinfo = file_get_contents(dp_get_data_dir().'/cli-phpinfo.html');
    if (strpos($phpinfo, '<body') === false) {
        header('Content-Type: text/plain');
        header('Content-Disposition: inline; filename=error.log.txt');
    }
    echo $phpinfo;
} else {
    phpinfo();
}
