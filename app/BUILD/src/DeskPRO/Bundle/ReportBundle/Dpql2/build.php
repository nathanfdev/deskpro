<?php

if (php_sapi_name() !== 'cli') {
    die('CLI only');
}

ini_set('display_errors', true);
chdir(__DIR__);
require './build-lexer.php';
require './build-parser.php';
