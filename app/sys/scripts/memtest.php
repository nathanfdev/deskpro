<?php if (!defined('DP_ROOT')) exit('No access');
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage SystemScripts
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

$memlimit = ini_get('memory_limit');
if (!$memlimit || $memlimit == -1) {
	$memlimit = 0;
	$memlimit_mb = 0;
} else {
	$last = strtolower($memlimit[strlen($memlimit)-1]);
	$memlimit = (int)$memlimit;
	switch($last) {
		case 'g': $memlimit *= 1024;
		case 'm': $memlimit *= 1024;
		case 'k': $memlimit *= 1024;
	}

	$memlimit_mb = round($memlimit / 1024 / 1024, 2);
}

if ($memlimit) {
	echo "Expected memory limit of {$memlimit} ({$memlimit_mb} MB)<br />";
} else {
	echo "No memory limit defined in PHP configuration. Testing with 500 MB.<br />";
	$memlimit = 500 * 1024 * 1024;
	$memlimit_mb = 500;
}

$mem = memory_get_usage();
$mem_mb = round($mem / 1024 / 1024, 2);
echo "Start: {$mem} ($mem_mb <B)<br />";

$counter = 0;
while (true) {
	$counter += 102400;

	$fill = str_repeat('x', $counter * 100);
	$mem = memory_get_usage();
	$mem_mb = round($mem / 1024 / 1024, 2);
	echo "Reached: {$mem} ($mem_mb MB)<br />";
	unset($fill);

	if ($mem_mb + 8 >= $memlimit_mb) {
		break;
	}
}

echo "<br />";
echo "If you can see this, then the server can allocate something close to " . $memlimit_mb . " MB";
