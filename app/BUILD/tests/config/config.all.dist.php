<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

# Test Config
#
# The tests need this config file to run.
#
# To run the tests, you need to do the following:
#
# 1. rename this file to "config.all.php"
# 2. ensure the database config below are correct
# 3. ensure the DP_E2E_BASE_URL is correct (used in e2e tests)
# 3. Remember that the test db will be deleted and
#    reconstructed on every test run. Make sure it
#    is NOT set to your dev database.
#

$CONFIG = [];

$process            = getenv('DP_PARALLEL_TESTING_PROCESS_NUM');
$dbPostfix          = $process ? "_{$process}" : '';
$dbName             = getenv('DATABASE_PREFIX') ?: 'deskpro_test';
$CONFIG['database'] = [
    'host'     => '127.0.0.1',
    'user'     => 'deskpro',
    'password' => 'deskpro',
    'dbname'   => $dbName.$dbPostfix,
];

$CONFIG['paths'] = [
    'php_path'       => 'php',
    'mysqldump_path' => 'mysqldump',
    'mysql_path'     => 'mysql',
];

$CONFIG['logs'] = [
    'log_level'           => 'debug',
    'log_level_threshold' => 'debug',
];

$CONFIG['env'] = [
    'set_umask'   => 0000,
    'environment' => 'test',
    'debug_mode'  => true,
];

$CONFIG['settings']['disable_outgoing_email'] = true;
