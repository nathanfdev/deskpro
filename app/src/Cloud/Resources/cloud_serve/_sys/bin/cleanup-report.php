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
 * Script that looks at the database and filesystem to determine which databases
 * or filesystem directories should be removed.
 *
 * Usage: cleanup-report.php [options]
 * Options:
 *     --outfile    Output machine data to a file
 *     --logfile    Output status to logfile
 *     --verbose    Output info about current processing as it goes (does not work with --machine).
 *     --machine    Machine output (e.g., suitable for automated tool)
 *     --just-db    Just the database report
 *     --just-fs    Just the filesystem report
 *
 * @package DeskPRO_Cloud
 */

########################################################################
# Init
########################################################################

require __DIR__.'/../CloudConfig.php';

#------------------------------
# Normalize env
#------------------------------

setlocale(LC_CTYPE, 'C');
date_default_timezone_set('UTC');
ini_set('default_charset', 'UTF-8');
set_time_limit(0);

$t_start = microtime(true);

########################################################################
# Sort out args
########################################################################

$do_db      = true;
$do_fs      = true;
$is_machine = false;
$is_verbose = false;
$outfile    = null;
$logfile    = null;

$options = getopt('vfdo:l:', array(
	'machine::',
	'just-db',
	'just-fs',
	'verbose',
	'logfile:',
	'outfile:',
));

if (!$options) {
	echo "Usage: cleanup-report.php [--machine] [--just-db] [--just-fs] [--verbose] [--logfile] [--outfile]";
}

if ($options['d'] || $options['just-db']) {
	$do_db = true;
	$do_fs = true;
} elseif ($options['f'] || $options['just-fs']) {
	$do_db = false;
	$do_fs = true;
}

if ($options['verbose'] || $options['v']) {
	$is_verbose = true;
}

if ($options['m'] || $options['machine']) {
	$is_machine = true;
	$is_verbose = false;
}

if ($options['o'] || $options['outfile']) {
	$outfile = $options['o'] ?: $options['outfile'];
}

if ($options['l'] || $options['logfile']) {
	$logfile = $options['l'] ?: $options['logfile'];
}

$cloud_config = require __DIR__.'/../config.php';

########################################################################
# Util
########################################################################

function dp_logf($msg)
{
	global $logfile, $is_verbose;

	if (!$is_verbose && !$logfile) {
		return;
	}

	$args = func_get_args();
	array_shift($args);

	$msg = vsprintf($msg, $args);
	$msg = trim($msg);
	$msg .= "\n";

	if ($is_verbose) {
		echo $msg;
	}
	if ($logfile) {
		file_put_contents($logfile, $msg, \FILE_APPEND);
	}
}


########################################################################
# Run tasks
########################################################################

dp_logf("Fetching site data ...");
$t = microtime(true);

$site_domains    = array();
$site_domains_us = array();
$site_db_users   = array();
$site_db_names   = array();

$db = CloudConfig::getDb();
$st = $db->prepare("
	SELECT cloud_sites.master_domain, cloud_sites.db_user, cloud_sites.db_name,
	FROM cloud_sites
	ORDER BY cloud_sites.id ASC
");
$st->execute();

while ($r = $st->fetch(\PDO::FETCH_ASSOC)) {
	$site_domains[]    = $r['master_domain'];
	$site_domains_us[] = str_replace('.', '_', $r['master_domain']);
	$site_db_users[]   = $r['db_user'];
	$site_db_names[]   = $r['db_name'];
}

$site_domains    = array_combine($site_domains, $site_domains);
$site_domains_us = array_combine($site_domains_us, $site_domains_us);
$site_db_users   = array_combine($site_db_users, $site_db_users);
$site_db_names   = array_combine($site_db_names, $site_db_names);

dp_logf("\t-> Loaded %d sites in %.3fs", count($site_domains), microtime(true)-$t);


########################################################################
# Process Filesystem
########################################################################

$cleanup_dirs = array();

if ($do_fs) {
	dp_logf("Checking filesystem data directories ...");
	$t = microtime(true);

	$dir = dir($cloud_config['datastore_path']);

	while (($f = $dir->read()) !== false) {
		$f_path = $dir->path . '/' . $f;
		if ($f == '.' || $f == '..' || !is_dir($f_path) || $f == '_cloud') continue;

		if (!isset($site_domains_us[$f])) {
			dp_logf("\t-> Found stale directory: %s", $f);
			$cleanup_dirs[] = $f_path;
		}
	}

	dp_logf("Finished scanning filesystem in %.3fs. Found %d stale directories.", microtime(true)-$t, count($cleanup_dirs));
}

########################################################################
# Process Databases
########################################################################

$cleanup_databases = array();

if ($do_db) {
	dp_logf("Checking databases ...");
	$t = microtime(true);

	$st = $db->prepare("SHOW DATABASES");
	$st->execute();

	while ($db_name = $st->fetchColumn(0)) {
		if (strpos($db_name, 'dp_cloud_') !== 0) {
			continue;
		}
		if (!isset($site_db_names[$db_name])) {
			dp_logf("\t-> Found stale database: %s", $f);
			$cleanup_databases[] = $db_name;
		}
	}

	dp_logf("Finished scanning databases in %.3fs. Found %d stale databases.", microtime(true)-$t, count($cleanup_databases));
}


########################################################################
# Process Database Users
########################################################################

$cleanup_database_users = array();

if ($do_db) {
	dp_logf("Checking databases users ...");
	$t = microtime(true);

	$st = $db->prepare("SELECT * FROM `mysql`.`user`");
	$st->execute();

	while ($userinfo = $st->fetchColumn(0)) {
		if (strpos($userinfo['User'], 'dp_cloud_') !== 0) {
			continue;
		}

		if (!isset($site_db_users[$userinfo['User']])) {
			dp_logf("\t-> Found stale database user: %s@%s", $userinfo['User'], $userinfo['Host']);
			$cleanup_database_users[] = array(
				'user' => $userinfo['User'],
				'host' => $userinfo['Host'],
			);
		}
	}

	dp_logf("Finished scanning databases in %.3fs. Found %d stale database users.", microtime(true)-$t, count($cleanup_database_users));
}

########################################################################
# Report
########################################################################

dp_logf("Scan complete in %.3f", microtime(true)-$t_start);

$machine = json_encode(array(
	'cleanup_dirs'           => $cleanup_dirs,
	'cleanup_databases'      => $cleanup_databases,
	'cleanup_database_users' => $cleanup_database_users,
));

if ($is_machine) {
	echo $machine;
} else {
	if ($is_verbose) {
		echo "\n\n";
	}

	if (!$cleanup_dirs && !$cleanup_databases && !$cleanup_database_users) {
		echo "No resources to clean up.\n";
	} else {
		if ($cleanup_dirs) {
			printf("Found %d stale directories:\n", count($cleanup_dirs));
			echo "\t" . implode("\n\t", $cleanup_dirs);
			echo "\n\n";
		}
		if ($cleanup_databases) {
			printf("Found %d stale databases:\n", count($cleanup_databases));
			echo "\t" . implode("\n\t", $cleanup_databases);
			echo "\n\n";
		}
		if ($cleanup_database_users) {
			printf("Found %d stale database users:\n", count($cleanup_database_users));
			echo "\t" . implode("\n\t", $cleanup_database_users);
			echo "\n\n";
		}
	}
}

if ($outfile) {
	file_put_contents($outfile, $machine);
}