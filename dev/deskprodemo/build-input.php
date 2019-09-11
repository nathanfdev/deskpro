<?php
// This file reads the BUILD INPUT from https://builder.deskprodemo.com/
// Other config files can include this to get the value:
// $buildInput = include('build-input.php');

if (!isset($GLOBALS['__DESKPRO_DEMO_BUILD_INPUT'])) {
    $GLOBALS['__DESKPRO_DEMO_BUILD_INPUT'] = json_decode(file_get_contents(__DIR__.'/build-input.json'), true);
}

return $GLOBALS['__DESKPRO_DEMO_BUILD_INPUT'];
