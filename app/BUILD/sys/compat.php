<?php

if (function_exists('curl_init')) {
    foreach ([
        'CURLOPT_CAINFO',
        'CURLOPT_SSL_VERIFYPEER',
        'CURLOPT_SSL_VERIFYHOST',
    ] as $name) {
        if (!defined($name)) {
            define($name, '');
        }
    }
}

// PHP bug in some versions of PHP where gzopen is replaced by gzopen64
// https://bugs.php.net/bug.php?id=53829
if (extension_loaded('zlib') && !function_exists('gzopen') && function_exists('gzopen64')) {
    function gzopen()
    {
        $args = func_get_args();

        return call_user_func_array('gzopen64', $args);
    }
}
