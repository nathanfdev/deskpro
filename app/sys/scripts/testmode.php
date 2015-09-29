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

function outStatus($code, $status_code, $exit_code = 0)
{
    if (isset($_GET['html'])) {
        header('Content-Type: text/html');

        // Clear cookies
        if (isset($_GET['clean'])) {
            foreach ($_COOKIE as $name => $x) {
                setcookie($name, '', 1415900000);
                setcookie($name, '', 1415900000, '/');
            }
        }

        echo '<div id="status">'.$code.'</div>';
        echo '<div id="status_code">'.$status_code.'</div>';
    } else {
        header('Content-Type: text/plain');
        echo 'status('.$code.')';
        echo "\n";
        echo 'status_code('.$status_code.')';
    }

    exit;
}

if ((!defined('DP_TESTING_MODE_ALLOW') || !DP_TESTING_MODE_ALLOW) && !is_file(DP_WEB_ROOT.'/running_tests.trigger')) {
    outStatus('DP_TESTING_MODE_ALLOW_FAIL', 'fail');
}

#------------------------------
# Enable testing
#------------------------------

if (isset($_GET['disable'])) {
    unlink(DP_WEB_ROOT.'/running_tests.trigger');

    if (is_file(DP_WEB_ROOT.'/running_tests.trigger')) {
        outStatus('DP_TESTING_MODE_FAILED_TRIGGER', 'fail');
    }

    outStatus('DP_TESTING_MODE_DISABLED', 'ok');
} else {
    $php     = @$DP_CONFIG['php_path'] ?: 'php';
    $path    = DP_ROOT;
    $setname = escapeshellarg(@$_GET['setname'] ?: '');

    $cmd = "$php $path/testing/bin/reset-product-db $setname";
    echo "> $cmd\n";

    $ret = null;
    passthru($cmd, $ret);

    echo "\n\n";

    if ($ret != 0) {
        outStatus('DP_TESTING_MODE_FAILED_DB', 'fail');
    }

    file_put_contents(DP_WEB_ROOT.'/running_tests.trigger', time());
    if (!is_file(DP_WEB_ROOT.'/running_tests.trigger')) {
        outStatus('DP_TESTING_MODE_FAILED_TRIGGER', 'fail');
    }

    outStatus('DP_TESTING_MODE_ENABLED', 'ok');
}
