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
 * Runs move-site-db.php on all sites in the db
 *
 * Usage: move-site-db-all.php
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
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);


########################################################################
# Run tasks
########################################################################

$db = CloudConfig::getDb();
$migrate_db_config = CloudConfig::getConfig('migrate_db');

$st = $db->prepare("
	SELECT
		cloud_sites.*
	FROM cloud_sites
	WHERE cloud_sites.build_number > 0 AND cloud_sites.in_use = 1 AND cloud_sites.db_host != '{$migrate_db_config['host']}'
	ORDER BY cloud_sites.id ASC
");

$st->execute();
$sites = $st->fetchAll(\PDO::FETCH_ASSOC);

printf("--------------- RUN ALL :: BEGIN (%d sites) ---------------\n", count($sites));

#------------------------------
# Run sites
#------------------------------

$time_begin = microtime(true);

foreach ($sites as $siteinfo) {
	$site_time_begin = microtime(true);
	printf("--- BEGIN SITE %d %s ---\n", $siteinfo['id'], $siteinfo['master_domain']);

	$cmd = "php move-site-db.php --dpc-site-id {$siteinfo['id']}";

	chdir(__DIR__);

	$ret = null;
	passthru($cmd, $ret);

	if ($ret) {
		echo "!!! DETECTED ERROR STATUS !!!\n";
		exit;
	}

	printf("\n--- END SITE %d %s (took %.4f s) ---\n", $siteinfo['id'], $siteinfo['master_domain'], microtime(true) - $site_time_begin);
}

printf("--------------- RUN ALL END (took %.4f s) ---------------\n", microtime(true) - $time_begin);
