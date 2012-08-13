#!/usr/bin/env php
<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * Script that processes a batch of site cron jobs one after the other.
 *
 * Note that unlike procmail.php, this is designed to be run IN PLACE due
 * to dependence on other DeskPRO source and library files. This means that if there
 * multiple cron processing servers, the entire DeskPRO file distribution should be
 * copied.
 *
 * This command should be called with a range argument like this:
 *     cron-run.php 1-10
 * This will run sites 1-10
 *
 * Everything after a -- will be passed to the individual cron scripts
 *   cron-run.php 1-10 -- --verbose -f
 *
 * Note that the --dpc-site-id parameter is automatically appended when executing the individual cron scripts
 *
 * @package DeskPRO_Cloud
 */

########################################################################
# Init
########################################################################

define('DP_ROOT', realpath(__DIR__ . '/../../../'));
require DP_ROOT.'/app/src/Cloud/Resources/config/config.php';
require(DP_ROOT . '/sys/bootstrap-dev.php');

#------------------------------
# Normalize env
#------------------------------

setlocale(LC_CTYPE, 'C');
date_default_timezone_set('UTC');
ini_set('default_charset', 'UTF-8');

\Orb\Util\Strings::setPhpUtf8Dir(DP_ROOT.'/vendor/php-utf8');

########################################################################
# Sort out args
########################################################################

$args = $_SERVER['argv'];
array_shift($args); // shift off this filename

#------------------------------
# Range to process
#------------------------------

$range = array_shift($args);
if (!$range) {
	echo "This command must be called with a range of IDs: cron-run.php 1-10\n";
	exit(1);
}

if (!strpos($range, '-')) {
	$range_start = $range_end = $range;
} else {
	list ($range_start, $range_end) = explode('-', $range);
}

if (!ctype_digit($range_start) || !ctype_digit($range_end)) {
	echo "This command must be called with a range of IDs: cron-run.php 1-10\n";
	exit(1);
}

#------------------------------
# Verbose
#------------------------------

$verbose = false;
if (($k = array_search('--verbose', $args)) !== false) {
	$verbose = true;
}

#------------------------------
# Additional args to append
#------------------------------

$get_pass_args = array();

if (($k = array_search(' -- ', $args)) !== false) {
	$get_pass_args = array_slice($args, $k);
}

// Always send the site id
$pass_args = array('--dpc-site-id', '%DPC_SITE_ID%');

foreach ($get_pass_args as $x) {
	$pass_args[] = escapeshellarg($x);
}

########################################################################
# Run tasks
########################################################################

$db = new \PDO();







