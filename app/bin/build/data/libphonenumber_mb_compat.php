<?php

/*
 * This file is appended to /app/vendor-src/libphonenumber/src/libphonenumber/PhoneNumberUtil.php
 * to make libphonenumber work when mbstring is not installed.
 *
 * See hack-vendors.sh
 */

if (!function_exists('mb_strlen')) {
    function mb_strlen($s)
    {
        return strlen($s);
    }
}

if (!function_exists('mb_substr')) {
    function mb_substr($s, $start, $len = null)
    {
        return substr($s, $start, $len);
    }
}

if (!function_exists('mb_strtoupper')) {
    function mb_strtoupper($s)
    {
        return strtoupper($s);
    }
}

// DESKPRO_MODIFIED
