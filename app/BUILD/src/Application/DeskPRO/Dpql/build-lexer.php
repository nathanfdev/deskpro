<?php

if (php_sapi_name() !== 'cli') {
    die('CLI only');
}

ini_set('display_errors', true);

chdir(__DIR__);

if (!file_exists('PHP/LexerGenerator.php')) {
    echo 'LexerGenerator is not installed';
    exit;
}

require_once 'PHP/LexerGenerator.php';
$a = new PHP_LexerGenerator('Lexer.plex');

$contents = file_get_contents('Lexer.php');
//$contents = preg_replace('#(throw new\s+)(Exception)#i', '$1\\\\$2', $contents);
$contents = preg_replace_callback(
    '#('.preg_quote('$yy_global_pattern = \'').')(.*)'.'(\';)#siU',
    function ($match) {
        return $match[1].str_replace("'", "\\'", $match[2]).$match[3];
    },
    $contents
);
file_put_contents('Lexer.php', $contents);

echo 'Lexer build complete.'.PHP_EOL;
