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

$is_authed = false;
if ((isset($_GET['_']) || isset($_COOKIE['dp_sysscript_'.$_GET['_sys']])) && file_exists(DP_CONFIG_FILE)) {
    $check_fn = function ($token, $secret) {
        // Check to make sure its a valid format
        if (substr_count($token, '-') != 2) {
            return false;
        }

        list($expire_time_enc, $rand_str, $hash) = explode('-', $token, 3);

        // Check the hash first
        $check_hash = sha1($secret.$expire_time_enc.$rand_str);

        if ($check_hash != $hash) {
            return false;
        }

        // Check the time now
        if ($expire_time_enc != '0') {
            $expire_time = base_convert($expire_time_enc, 36, 10);
            if (time() > $expire_time) {
                return false;
            }
        }

        return true;
    };

    if (isset($_GET['_'])) {
        $is_authed = $check_fn($_GET['_'], md5_file(DP_CONFIG_FILE).$_GET['_sys']);
        if ($is_authed) {
            setcookie('dp_sysscript_'.$_GET['_sys'], $_GET['_'], time() + 18000, '/');
        }
    } elseif (isset($_COOKIE['dp_sysscript_'.$_GET['_sys']])) {
        $is_authed = $check_fn($_COOKIE['dp_sysscript_'.$_GET['_sys']], md5_file(DP_CONFIG_FILE).$_GET['_sys']);
    }
}

switch ($_GET['_sys']) {
    case 'blank':
        break;

    case 'memtest':
        if (defined('DPC_IS_CLOUD')) {
            exit;
        }
        if (!$is_authed) {
            die('Invalid auth code.');
        }
        require DP_ROOT.'/sys/scripts/memtest.php';
        break;

    case 'errorlog':
        if (defined('DPC_IS_CLOUD')) {
            exit;
        }
        if (!$is_authed) {
            die('Invalid auth code.');
        }
        require DP_ROOT.'/sys/scripts/errorlog.php';
        break;

    case 'check':
        if (defined('DPC_IS_CLOUD')) {
            exit;
        }
        require DP_ROOT.'/sys/scripts/check.php';
        break;

    case 'phpinfo':
        if (defined('DPC_IS_CLOUD')) {
            exit;
        }
        require DP_ROOT.'/sys/scripts/phpinfo.php';
        break;

    case 'apc':
        if (defined('DPC_IS_CLOUD')) {
            exit;
        }
        if (!$is_authed) {
            die('Invalid auth code.');
        }
        require DP_ROOT.'/sys/scripts/apc.php';
        break;

    case 'opcache':
        if (defined('DPC_IS_CLOUD')) {
            exit;
        }
        if (!$is_authed) {
            die('Invalid auth code.');
        }
        require DP_ROOT.'/sys/scripts/opcache.php';
        break;

    case 'apcclear':
        if (defined('DPC_IS_CLOUD')) {
            exit;
        }
        if (!$is_authed) {
            die('Invalid auth code.');
        }
        require DP_ROOT.'/sys/scripts/apcclear.php';
        break;

    case 'wincache':
        if (defined('DPC_IS_CLOUD')) {
            exit;
        }
        if (!$is_authed) {
            die('Invalid auth code.');
        }
        require DP_ROOT.'/sys/scripts/wincache.php';
        break;

    case 'checkurl':
        if (defined('DPC_IS_CLOUD')) {
            exit;
        }
        require DP_ROOT.'/sys/scripts/checkurl.php';
        break;

    case 'checkurlpath':
        if (defined('DPC_IS_CLOUD')) {
            exit;
        }
        require DP_ROOT.'/sys/scripts/checkurlpath.php';
        break;

    case 'check_http_method':
        if (defined('DPC_IS_CLOUD')) {
            exit;
        }
        require DP_ROOT.'/sys/scripts/check_http_method.php';
        break;

    case 'dev_run_migrations':
        if (defined('DPC_IS_CLOUD')) {
            exit;
        }
        if (!$is_authed) {
            die('Invalid auth code.');
        }
        require DP_ROOT.'/sys/scripts/dev_run_migrations.php';
        break;

    case 'savemail':
        require DP_ROOT.'/sys/scripts/savemail.php';
        break;

    case 'save_failed_sendmail':
        require DP_ROOT.'/sys/scripts/failed_sendmail_job.php';
        break;

    case 'chat_status':
        require DP_ROOT.'/sys/scripts/chat_status.php';
        break;

    case 'ping':
        require DP_ROOT.'/sys/scripts/ping.php';
        break;

    case 'rewrite_loop_detected':
        require DP_ROOT.'/sys/scripts/rewrite_loop_detected.php';
        break;

    case 'licinfo':
        require DP_ROOT.'/sys/scripts/licinfo.php';
        break;

    case 'smtp_event':
        require DP_ROOT.'/sys/scripts/smtp_event.php';
        break;

    case 'stats':
        require DP_ROOT.'/sys/scripts/stats.php';
        break;

    case 'message':
        require DP_ROOT.'/sys/scripts/message.php';
        break;

    case 'testmode':
        require DP_ROOT.'/sys/scripts/testmode.php';
        break;
}
