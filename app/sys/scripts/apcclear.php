<?php if (!defined('DP_ROOT')) exit('No access');

if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    apc_clear_cache('user');
    echo "APC Cache Cleared";
} elseif (extension_loaded('wincache')) {
    wincache_ucache_clear();
    echo "WinCache User Cache Cleared";
} else {
    echo "APC not installed";
}

#------------------------------
# Delete the apc clear trigger
#------------------------------

require_once DP_ROOT.'/sys/load_config.php';
dp_load_config();
@unlink(dp_get_tmp_dir() . '/apc-clear.trigger');
