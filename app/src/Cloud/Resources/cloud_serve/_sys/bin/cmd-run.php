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
 * Script that runs a command under the context of each site and shows the output.

 * This command should be called like this:
 *   cmd-run.php -- any parameters to pass to cmd.php
 *
 * For example:
 *   cmd-run.php -- dp:test --verbose
 *
 * Anything after the -- is passed to the cmd.php call.
 *
 * Usage: cmd-run.php -- <command>
 *
 * @package DeskPRO_Cloud
 */

########################################################################
# Init
########################################################################

require __DIR__.'/../CloudConfig.php';
require __DIR__.'/../lib/Process.php';

$DO_REPORT_LOG = false;

#------------------------------
# Normalize env
#------------------------------

setlocale(LC_CTYPE, 'C');
date_default_timezone_set('UTC');
ini_set('default_charset', 'UTF-8');
set_time_limit(0);

########################################################################
# Sort out args
########################################################################

$args = $_SERVER['argv'];
array_shift($args); // shift off this filename

#------------------------------
# Additional args to append
#------------------------------

$get_pass_args = array();

if (($k = array_search('--', $args)) !== false) {
	$get_pass_args = array_slice($args, $k+1);
	$args = array_slice($args, 0, $k);
}

// Always send the site id
$pass_args = array('--dpc-site-id', '%DPC_SITE_ID%');

foreach ($get_pass_args as $x) {
	$pass_args[] = escapeshellarg($x);
}

$pass_args = implode(" ", $pass_args);

########################################################################
# Util
########################################################################

$dp_log_messages = array();

function dp_log($msg, $nl = true)
{
	global $dp_log_messages;

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

$db = CloudConfig::getDb();

$st = $db->prepare("
	SELECT
		cloud_sites.*,
		cloud_accounts.id AS account_id, cloud_accounts.agents, cloud_accounts.is_demo, UNIX_TIMESTAMP(cloud_accounts.date_demo_expire) AS demo_expire_at
	FROM cloud_sites
	LEFT JOIN cloud_accounts ON cloud_accounts.cloud_site_id = cloud_sites.id
	WHERE cloud_sites.build_number > 0 AND cloud_sites.sys_disabled IS NULL AND cloud_sites.in_use = 1
	ORDER BY cloud_sites.id ASC
");

$st->execute();
$sites = $st->fetchAll(\PDO::FETCH_ASSOC);

dp_logf("--------------- RUN ALL :: BEGIN (%d sites) ---------------", count($sites));

#------------------------------
# Run sites
#------------------------------

$time_begin = microtime(true);

foreach ($sites as $siteinfo) {
	$site_time_begin = microtime(true);
	dp_logf("--- BEGIN SITE %d %s ---", $siteinfo['id'], $siteinfo['master_domain']);

	$pass_args_set = $pass_args;
	$pass_args_set = str_replace('%DPC_SITE_ID%', $siteinfo['id'], $pass_args_set);

	$cmd = "php cmd.php $pass_args_set";
	dp_log("\tCommand: $cmd");
	$proc = new Process($cmd, CloudConfig::getBuildsPath() . '/' . $siteinfo['build_number']);
	$proc->setTimeout(900);

	try {
		$proc->run(function($type, $data) {
			dp_log(sprintf("\t%s\n", str_replace("\n", "\n\t", trim($data))), false);
		});
	} catch (\RuntimeException $e) {
		dp_log("!!! PROCESS TIMED OUT !!!");
		$DO_REPORT_LOG = true;
	}

	if (!$proc->isSuccessful()) {
		dp_log("!!! DETECTED ERROR STATUS !!!");
		$DO_REPORT_LOG = true;
	}

	dp_logf("--- END SITE %d %s (took %.4f s) ---", $siteinfo['id'], $siteinfo['master_domain'], microtime(true) - $site_time_begin);
}

dp_logf("--------------- RUN ALL END (took %.4f s) ---------------", microtime(true) - $time_begin);