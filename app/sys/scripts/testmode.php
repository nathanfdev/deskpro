<?php if (!defined('DP_ROOT')) exit('No access');

require DP_ROOT.'/sys/load_config.php';
dp_load_config();

#------------------------------
# Verify auth
#------------------------------

function outStatus($code, $status_code, $exit_code = 0)
{
    if (isset($_GET['html'])) {
        header("Content-Type: text/html");

        // Clear cookies
        if (isset($_GET['clean'])) {
            foreach ($_COOKIE as $name => $x) {
                setcookie($name, '', 1415900000);
                setcookie($name, '', 1415900000, '/');
            }
        }

        echo '<div id="status">' . $code . '</div>';
        echo '<div id="status_code">' . $status_code . '</div>';
    } else {
        header("Content-Type: text/plain");
        echo 'status(' . $code . ')';
        echo "\n";
        echo 'status_code(' . $status_code . ')';
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
    $php = @$DP_CONFIG['php_path'] ?: "php";
    $path = DP_ROOT;
    $setname = escapeshellarg(@$_GET['setname'] ?: "");

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
