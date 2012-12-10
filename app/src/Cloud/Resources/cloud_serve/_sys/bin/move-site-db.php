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

if (php_sapi_name() != 'cli') {
	echo "This script must only be run from the CLI.\n";
	echo "Contact support@deskpro.com if you require assistance.\n";
	exit(1);
}

/**
 * This script migrates the database of a single site to another server.
 * Use the move-site-db-all.php file to move all sites.
 *
 * Operations performed:
 * 1) Create a new database/user on the new mysql server
 * 2) Shut down the site using the filesystem sys_disabled.trigger file
 * 3) mysqldump the database
 * 4) Import the dump on the new server
 * 5) Update the site record to point to the new database
 * 6) Re-enable the site by removing the sys_disabled.trigger file
 *
 * The cloud_serve/_sys/config.php file is expected to have a 'migrate_db' option
 * which defines the control user used on the new server:
 *
 * <code>
 * // ...
 * 'migrate_db' => array(
 *     'host'     => 'server_host',
 *     'user'     => 'root',
 *     'password' => 'root',
 * )
 * // ..
 * </code>
 *
 * Usage: move-site-db.php --dpc-site-id 55
 *
 * @package DeskPRO_Cloud
 */

########################################################################
# Init
########################################################################

define('DP_TIME_START', time());
require __DIR__.'/../CloudConfig.php';
require __DIR__.'/../lib/Process.php';

#------------------------------
# Normalize env
#------------------------------

setlocale(LC_CTYPE, 'C');
date_default_timezone_set('UTC');
ini_set('default_charset', 'UTF-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);

define('DP_START_TIME', time());

########################################################################
# Sort out config
########################################################################

$migrate_db_config = CloudConfig::getConfig('migrate_db');

if (!$migrate_db_config || empty($migrate_db_config['host']) || empty($migrate_db_config['user']) || empty($migrate_db_config['password'])) {
	echo "Missing migrate_db from config.php\n";
	exit(1);
}

try {
	$migrate_db = new \PDO(
		sprintf("mysql:host=%s", $migrate_db_config['host']),
		$migrate_db_config['user'],
		$migrate_db_config['password']
	);
} catch (\PDOException $e) {
	printf("Error connecting to migrate DB: %s %s\n", $e->getCode(), $e->getMessage());
	exit(1);
}

########################################################################
# Sort out args
########################################################################

$site_id = null;

if (($k = array_search('--dpc-site-id', $_SERVER['argv'])) !== false) {
	$site_id = (int)$_SERVER['argv'][$k+1];
}

if (!$site_id) {
	echo "Provide a Site ID with --dpc-site-id\n";
	exit(1);
}

$cloud_db = CloudConfig::getDb();
$st = $cloud_db->prepare("
	SELECT cloud_sites.*
	FROM cloud_sites
	WHERE cloud_sites.id = ?
");

$st->execute(array($site_id));
$siteinfo = $st->fetch(\PDO::FETCH_ASSOC);

if (!$siteinfo) {
	echo "Invalid Site ID\n";
	exit(1);
}

if ($siteinfo['db_host'] == $migrate_db_config['host']) {
	echo "Site is already using new host.\n";
	exit(1);
}

########################################################################
# Util
########################################################################

$dp_log_messages = array();

set_exception_handler(function (\Exception $e) {
	dp_logf("EXCEPTION %s: %s %s", get_class($e), $e->getCode(), $e->getMessage());
	dp_log($e->getTraceAsString());
	exit(1);
});

function dp_log($msg, $nl = true)
{
	global $dp_log_messages, $site_id;

	if ($nl) {
		$msg .= "\n";
	}

	$msg = "[" . date('Y-m-d H:i:s') . "] " . $msg;

	$dp_log_messages[] = $msg;

	echo $msg;

	// Always save log now
	file_put_contents(
		CloudConfig::getDatastorePath() . "/_cloud/move-site-db." . $site_id . ".log",
		$msg,
		\FILE_APPEND | \LOCK_EX
	);
}

function dp_logf($msg)
{
	$args = func_get_args();
	array_shift($args);

	dp_log(vsprintf($msg, $args));
}

dp_logf("########## BEGIN SITE %d %s ##########", $site_id, $siteinfo['master_domain']);

########################################################################
# Check site cron isnt running
########################################################################

// Need to wait for last cron runner to finish if its running

try {
	$site_db = new \PDO(
		sprintf("mysql:host=%s;dbname=", $siteinfo['db_host'], $siteinfo['db_name']),
		$siteinfo['db_user'],
		$siteinfo['db_password']
	);
} catch (\PDOException $e) {
	printf("Error connecting to site DB: %s %s\n", $e->getCode(), $e->getMessage());
	exit(1);
}

$st = $site_db->prepare("SELECT value FROM settings WHERE name = 'core.croncheck.dp-cron'");

$x = 0;
while (1) {
	dp_log("Checking if cron is running ...");
	$st->execute();

	if ($st->fetchColumn()) {
		if ($x++ > 10) {
			dp_log("Error: Cron taking too long. Crashed?");
			exit(1);
		}

		dp_log("\tRunning. Sleeping.");
		sleep(5);
	} else {
		break;
	}
}

########################################################################
# Get new database ready for action
########################################################################

dp_log("");
dp_log("--- Preparing remote database ---");
dp_logf("DB: %s    User: %s    Password: %s", $siteinfo['db_name'], $siteinfo['db_user'], $siteinfo['db_password']);

#------------------------------
# Check database
#------------------------------

dp_log("Ensuring remote database does not already exist ...");

$has = $migrate_db->query("SHOW DATABASES LIKE '{$siteinfo['db_name']}'")->fetch(\PDO::FETCH_NUM);

if ($has) {
	dp_log("FAILED: Remote database already exists");
	exit(1);
}

dp_log("\tOK");

#------------------------------
# Check user
#------------------------------

dp_log("Ensuring remote user does not already exist ...");

$has = $migrate_db->query("
	SELECT User
	FROM `mysql`.`user`
	WHERE User = '{$siteinfo['db_user']}'
")->fetch(\PDO::FETCH_NUM);

if ($has) {
	dp_log("FAILED: Remote user already exists");
	exit(1);
}

dp_log("\tOK");

#------------------------------
# Create database
#------------------------------

dp_log("Creating remote database");
$migrate_db->exec("CREATE DATABASE `{$siteinfo['db_name']}`");
dp_log("\tOK");

#------------------------------
# Create user
#------------------------------

dp_log("Creating user (1)");
$migrate_db->exec("GRANT ALL PRIVILEGES ON  `{$siteinfo['db_name']}`.* TO '{$siteinfo['db_user']}'@'%' IDENTIFIED BY '{$siteinfo['db_password']}'");
dp_log("\tOK");

dp_log("Creating user (2)");
$migrate_db->exec("GRANT ALL PRIVILEGES ON  `{$siteinfo['db_name']}`.* TO '{$siteinfo['db_user']}'@'localhost' IDENTIFIED BY '{$siteinfo['db_password']}'");
dp_log("\tOK");


########################################################################
# Disable and dump site
########################################################################

dp_log("");
dp_log("--- Disable and dump site ---");

$site_ds_path = CloudConfig::getConfig('datastore_path') . '/' . str_replace('.', '_', $siteinfo['master_domain']);

#------------------------------
# Disable site
#------------------------------

$sys_disabled_file = $site_ds_path . '/sys_disabled.trigger';

if (!file_put_contents($sys_disabled_file, 'upgrading')) {
	dp_log("Failed to save disabled trigger at $sys_disabled_file");
	exit(1);
}

$dump_path = $site_ds_path . '/dump.sql';

if (file_exists($dump_path)) {
	dp_log("Site dump already exists at $dump_path");
	exit(1);
}

#------------------------------
# Dump site
#------------------------------

$cmd = "mysqldump -u'{$siteinfo['db_user']}' -p'{$siteinfo['db_password']}' -h'{$siteinfo['db_host']}' --opt -Q --hex-blob '{$siteinfo['db_name']}' > '$dump_path'";

$t_start = microtime(true);
dp_logf("Dumping database with command: %s", $cmd);

$ret = null;
passthru($cmd, $ret);

dp_logf("\tCommand done in %.4f seconds", microtime(true) - $t_start);

if ($ret) {
	dp_log("ERROR: Command exited with error status: $ret");
	exit(1);
}

if (!file_exists($dump_path)) {
	dp_log("Dump file does not exist");
	exit;
}

dp_logf("Dump size: %d", filesize($dump_path));


########################################################################
# Import site into remote database
########################################################################

dp_log("");
dp_log("--- Import site to new database and re-enable ---");

#------------------------------
# Import dump
#------------------------------

$cmd = "mysql -u'{$siteinfo['db_user']}' -p'{$siteinfo['db_password']}' -h'{$migrate_db_config['host']}' '{$siteinfo['db_name']}' < '$dump_path'";

$t_start = microtime(true);
dp_logf("Restoring database with command: %s", $cmd);

$ret = null;
passthru($cmd, $ret);

dp_logf("\tCommand done in %.4f seconds", microtime(true) - $t_start);

if ($ret) {
	dp_log("ERROR: Command exited with error status: $ret");
	exit(1);
}

#------------------------------
# Update site record
#------------------------------

$rows = $cloud_db->exec("UPDATE cloud_sites SET db_host = '{$migrate_db_config['host']}' WHERE id = {$site_id} LIMIT 1");

if ($rows != 1) {
	dp_logf("Affected rows reported as %s, was there an error?", $rows);
	exit(1);
}

#------------------------------
# Re-enable the site
#------------------------------

if (!unlink($sys_disabled_file)) {
	dp_log("Failed to delete the sys_disabled.trigger file");
	exit(1);
}


########################################################################
# All done
########################################################################

dp_log("");
dp_log("--- DONE ---");

$time = time() - DP_START_TIME;

dp_logf("All steps finished successfully in %d seconds", $time);

exit(0);