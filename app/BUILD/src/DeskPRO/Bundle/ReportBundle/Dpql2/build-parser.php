<?php

if (php_sapi_name() !== 'cli') {
    die('CLI only');
}

ini_set('display_errors', true);

chdir(__DIR__);

register_shutdown_function('shutdown_function');

$_SERVER['argv'] = [basename(__FILE__), 'Parser.y'];
$_SERVER['argc'] = 2;

if (!file_exists('PHP/ParserGenerator.php')) {
    echo 'ParserGenerator is not installed';
    exit;
}

require_once 'PHP/ParserGenerator.php';
$me = new PHP_ParserGenerator();
$me->main(); // this calls exit so need to hack around that with a shutdown function

function shutdown_function()
{
    $prefixCode = 'namespace DeskPRO\Bundle\ReportBundle\Dpql2;

use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\DpqlStatementFactory;
';

    $contents = file_get_contents('Parser.php');
    $contents = preg_replace('#^(<\?php)#i', '$1'."\n$prefixCode", $contents);
    $contents = preg_replace('#(implements\s+)(ArrayAccess)#i', '$1\\\\$2', $contents);
    file_put_contents('Parser.php', $contents);

    echo 'Parser build complete.'.PHP_EOL;
}
