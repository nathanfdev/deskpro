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
 * Script that upgrades sites to a certain build.
 *
 * Note that unlike procmail.php, this is designed to be run IN PLACE. The path to CloudConfig
 * is expected to be one dir up.
 *
 * This command should be called with a single argument being the build to upgrade TO.
 *     cron-run.php 102
 * This will run every site that is older than build 102 through the upgrader to version 102.
 *
 * Usage: cloud-upgrade.php <to_version> [options]
 * Options:
 *     --dry-run             Dont actually do upgrades, just see what would happen
 *
 * @package DeskPRO_Cloud
 */

########################################################################
# Init
########################################################################

require __DIR__.'/../CloudConfig.php';
require __DIR__.'/../lib/Process.php';

#------------------------------
# Normalize env
#------------------------------

setlocale(LC_CTYPE, 'C');
date_default_timezone_set('UTC');
ini_set('default_charset', 'UTF-8');

########################################################################
# Sort out args
########################################################################

$args = $_SERVER['argv'];
array_shift($args); // shift off this filename

#------------------------------
# Build to process
#------------------------------

$build_num = array_shift($args);
if (!$build_num) {
	echo "This command must be called with a build number: cloud-upgrade.php 102\n";
	exit(1);
}

if (!ctype_digit($build_num)) {
	echo "This command must be called with a build number: cloud-upgrade.php 102\n";
	exit(1);
}

// Verify we have the source for this build
$build_dir = CloudConfig::getBuildsPath() . '/' . $build_num;

if (!is_dir($build_dir)) {
	echo "The build directory does not exist where it was expected: $build_dir\n";
	exit(1);
}

#------------------------------
# Dry run
#------------------------------

$dry_run = false;
if (($k = array_search('--dry-run', $args)) !== false) {
	$dry_run = true;
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

	echo $msg;
}

function dp_logf($msg)
{
	$args = func_get_args();
	array_shift($args);

	dp_log(vsprintf($msg, $args));
}

########################################################################
# Run tasks
########################################################################

$time_begin = microtime(true);
dp_logf("--------------- UPGRADE BEGIN (TO BUILD %d) : %s ---------------", $build_num, date('M j Y H:i'));

$db = CloudConfig::getDb();

$st = $db->prepare("
	SELECT
		cloud_sites.*,
		cloud_accounts.id AS account_id, cloud_accounts.agents, cloud_accounts.is_demo, UNIX_TIMESTAMP(cloud_accounts.date_demo_expire) AS demo_expire_at
	FROM cloud_sites
	LEFT JOIN cloud_accounts ON cloud_accounts.cloud_site_id = cloud_sites.id
	WHERE cloud_sites.build_number > 0 AND cloud_sites.build_number < :build_num AND cloud_sites.sys_disabled IS NULL AND cloud_sites.in_use = 1
	ORDER BY cloud_sites.id ASC
");
$st->execute(array(':build_num' => $build_num));

$sites = $st->fetchAll(\PDO::FETCH_ASSOC);

#------------------------------
# Run sites
#------------------------------

foreach ($sites as $siteinfo) {
	$site_time_begin = microtime(true);
	dp_logf("--- BEGIN SITE %d %s FROM BUILD %d ---", $siteinfo['id'], $siteinfo['master_domain'], $siteinfo['build_number']);

	$pass_args_set = "--dpc-site-id {$siteinfo['id']} --run-db-upgrade";

	$db->exec("UPDATE cloud_sites SET sys_disabled = 'upgrading' WHERE id = {$siteinfo['id']}");

	$cmd = "php upgrade.php $pass_args_set";
	dp_log("\tCommand: $cmd");
	$proc = new Process($cmd, CloudConfig::getBuildsPath() . '/' . $build_num);
	$proc->run(function($type, $data) {
		dp_log(sprintf("\t%s\n", str_replace("\n", "\n\t", trim($data))), false);
	});

	if (!$proc->isSuccessful()) {
		dp_log("!!! DETECTED ERROR STATUS !!!");
	} else {
		$db->exec("UPDATE cloud_sites SET build_number = $build_num, sys_disabled = NULL WHERE id = {$siteinfo['id']}");
	}

	dp_logf("--- END SITE %d %s (took %.4f s) ---", $siteinfo['id'], $siteinfo['master_domain'], microtime(true) - $site_time_begin);
}

dp_logf("--------------- UPGRADE DONE (took %.4f s) ---------------", microtime(true) - $time_begin);
