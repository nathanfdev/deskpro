<?php
/**
 * The path to the 'appfiles' directory. If you want to move the directory,
 * you must update this path.
 */
define('DP_ROOT', __DIR__ . '/appfiles');


require(DP_ROOT . '/sys/Kernel/Boot.php');
\DeskPRO\Kernel\Boot::bootWeb('prod', true);
