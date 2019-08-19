<?php

// A client access token for the demo license RMUK-9748-FFWM on stack eu1-test
$SETTINGS['dpss.access_token'] = 'd047631c-1fd3-4b0f-bc29-b6476cacb4d0';
$SETTINGS['dpss.auth_token']   = 'iv0tu1Sae1phe6weifeegheiY3ioqu5eid3';

// Voice -- enable creating custom twilio accounts
$SETTINGS['voice.private_accounts_enabled'] = true;

// Append overrides from build input
$buildInput = include(__DIR__.'/../build-input.php');
if (!empty($buildInput['SETTINGS'])) {
    $SETTINGS = array_merge($SETTINGS, $buildInput['SETTINGS']);
}
