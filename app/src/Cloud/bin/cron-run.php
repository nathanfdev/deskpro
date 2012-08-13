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

namespace Cloud\Bin;

use Cloud\CloudConfig;
use Symfony\Component\Process\Process;

if (php_sapi_name() != 'cli') {
	echo "This script must only be run from the CLI.\n";
	echo "Contact support@deskpro.com if you require assistance.\n";
	exit(1);
}

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
 * The command is meant to be run multiple times with different ranges so multiple sites are executed
 * in parallel.
 *
 * Everything after a -- will be passed to the individual cron scripts
 *   cron-run.php 1-10 -- --verbose -f
 *
 * Note that the --dpc-site-id parameter is automatically appended when executing the individual cron scripts
 *
 * Usage: cron-run.php <id1>-<id2> [options]
 * Options:
 *     --quiet               Do not output anything
 *     --force               Run even if proc-file exists and not timed out
 *     --proc-file           Path to a proc file that is used to determine if the command is still running.
 *                           By default this is placed in the data/tmp directory and named cloud-cron.XXX.time
 *     --proc-timeout        How many seconds until process is assumed crashed and the process resumes?
 *
 *     -- <cron options>     Any options specified after the double-dash will be passed onto the individual
 *                           Cron execution.
 *
 * @package DeskPRO_Cloud
 */

########################################################################
# Init
########################################################################

define('DP_ROOT', realpath(__DIR__ . '/../../../'));
define('DP_WEB_ROOT', realpath(__DIR__ . '/../../../../'));
require(DP_ROOT . '/sys/bootstrap-dev.php');
require DP_ROOT.'/src/Cloud/CloudConfig.php';

$DO_REPORT_LOG = false;

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
# Additional args to append
#------------------------------

$get_pass_args = array();

if (($k = array_search(' -- ', $args)) !== false) {
	$get_pass_args = array_slice($args, $k);
	$args = array_slice($args, 0, $k);
}

// Always send the site id
$pass_args = array('--dpc-site-id', '%DPC_SITE_ID%');

foreach ($get_pass_args as $x) {
	$pass_args[] = escapeshellarg($x);
}

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
# Quiet
#------------------------------

$is_quiet = false;
if (($k = array_search('--quiet', $args)) !== false) {
	$is_quiet = true;
}

#------------------------------
# Ignore running status file
#------------------------------

$is_force = false;
if (($k = array_search('--force', $args)) !== false) {
	$is_force = true;
}

#------------------------------
# Proc file path
#------------------------------

$proc_file = realpath(DP_ROOT . '/../data/tmp/cloud-cron.%RANGE_START%.%RANGE_END%.time');
if (($k = array_search('--proc-file', $args)) !== false && isset($args[$k+1])) {
	$proc_file = $args[$k+1];
}

$proc_file = str_replace(array('%RANGE_START%', '%RANGE_END%'), array($range_start, $range_end), $proc_file);

register_shutdown_function(function() use ($proc_file) {
	if (file_exists($proc_file)) {
		@unlink($proc_file);
	}
});

#------------------------------
# Proc file timeout
#------------------------------

$proc_timeout = 900;
if (($k = array_search('--proc-timeout', $args)) !== false && isset($args[$k+1])) {
	$proc_timeout = $args[$k+1];
}

########################################################################
# Util
########################################################################

$dp_log_messages = array();

function dp_log($msg, $nl = true)
{
	global $dp_log_messages, $is_quiet;

	if ($nl) {
		$msg .= "\n";
	}

	$dp_log_messages[] = $msg;

	if (!$is_quiet) {
		echo $msg;
	}
}

function dp_logf($msg)
{
	$args = func_get_args();
	array_shift($args);

	dp_log(vsprintf($msg, $args));
}

$time_begin = microtime(true);
dp_logf("--------------- CRON RUN BEGIN (ID %d-%d) : %s ---------------", $range_start, $range_end, date('M j Y H:i'));


########################################################################
# Ensure single process
########################################################################

if (file_exists($proc_file)) {
	$time = file_get_contents($proc_file);
	dp_logf("Task still running: %s", $time);

	$time = (int)$time;
	if (!$time || $time > $proc_timeout || $is_force) {
		dp_log("Task has timed out, restarting");
		unlink($proc_file);

		$DO_REPORT_LOG = true;
	}
}

file_put_contents($proc_file, time());


########################################################################
# Run tasks
########################################################################

$db = CloudConfig::getDb();

$st = $db->prepare("
	SELECT
		cloud_sites.*,
		cloud_accounts.id AS account_id, cloud_accounts.agents, cloud_accounts.is_demo, UNIX_TIMESTAMP(cloud_accounts.date_demo_expire) AS demo_expire_at
	FROM cloud_sites
	LEFT JOIN cloud_accounts ON cloud_accounts.cloud_site_id = cloud_sites.id
	WHERE cloud_sites.id BETWEEN :range_start AND :range_end
	LIMIT 1
");
$st->execute(array(':range_start' => $range_start, ':range_end' => $range_end));

$sites = $st->fetchAll(\PDO::FETCH_ASSOC);

#------------------------------
# Run sites
#------------------------------

foreach ($sites as $siteinfo) {
	$site_time_begin = microtime(true);
	dp_logf("--- BEGIN SITE %d %s ---", $siteinfo['id'], $siteinfo['master_domain']);

	$cmd = "php cron.php --dpc-site-id {$siteinfo['id']} --verbose $pass_args";
	dp_log("> $cmd");
	$proc = new Process($cmd, DP_WEB_ROOT);
	$proc->run(function($type, $data) {
		dp_logf("[%s] %s", $type, $data);
	});

	if (!$proc->isSuccessful()) {
		dp_log("!!! DETECTED ERROR STATUS !!!");
		$DO_REPORT_LOG = true;
	}

	dp_logf("--- END SITE %d %s (took %.4f s) ---", $siteinfo['id'], $siteinfo['master_domain'], microtime(true) - $site_time_begin);
}

dp_logf("--------------- CRON RUN END (ID %d-%d) : %s (took %.4f s) ---------------", $range_start, $range_end, date('M j Y H:i'), microtime(true) - $time_begin);

if ($DO_REPORT_LOG) {
	$dp_log_messages = implode('', $dp_log_messages);
	mail(
		CloudConfig::getErrorContact(),
		sprintf('[Cloud Cron] Processing Error. Batch %d-%d started at %s', $range_start, $range_end, date('M j Y H:i', (int)$time_begin)),
		$dp_log_messages,
		"From: cloud-cron@helium.serv.deskpro.com\r\n"
	);

	// Also save log to filesystem
	file_put_contents(
		DP_WEB_ROOT."/data/logs/cloud-cron.{$range_start}-{$range_end}." . str_replace('.', '_', microtime(true)) . ".log",
		$dp_log_messages
	);
}