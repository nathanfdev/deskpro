<?php

// 2. ensure the database config below are correct
// 3. ensure the DP_E2E_BASE_URL is correct (used in e2e tests)
// 4. Remember that the test db will be deleted and
//    reconstructed on every test run. Make sure it
//    is NOT set to your dev database.

defined('DP_BYPASS_TOKEN_AUTH') || define('DP_BYPASS_TOKEN_AUTH', 'test_bypass_token');

$CONFIG = [];

$process            = getenv('DP_PARALLEL_TESTING_PROCESS_NUM');
$dbPostfix          = $process ? "_{$process}" : '';
$dbName             = getenv('DATABASE_PREFIX') ?: 'deskpro_test';
$CONFIG['database'] = [
    'host'     => '127.0.0.1',
    'user'     => 'deskpro',
    'password' => 'deskpro',
    'dbname'   => $dbName.$dbPostfix,

    /*
        If you're using a recent Mysql you need to copy your password
        into a file for CLI usage or else you'll get messages like:
        [Warning] Using a password on the command line interface can be insecure.

        The file is a cnf file loaded in addition to your usual and should contain
        your credentials. E.g.:

        [client]
        user=myuser
        password=mypassword
    */
    // 'login-path' => '/path/to/mysql/login.cnf'
];

$CONFIG['paths'] = [
    'disable_path_validator' => false,
    'php_path'               => 'php',
    'mysqldump_path'         => 'mysqldump',
    'mysql_path'             => 'mysql',
];

$CONFIG['logs'] = [
    'log_level'           => 'debug',
    'log_level_threshold' => 'debug',
    'log_db_queries'      => true,
];

$CONFIG['env'] = [
    'set_umask'   => 0000,
    'environment' => 'test',
    'debug_mode'  => true,
];

$CONFIG['settings']['enable_experimental']            = ['all' => true];
$CONFIG['settings']['disable_outgoing_email']         = true;
$CONFIG['settings']['core.deskpro_url']               = 'http://develop74.dp.devput.com:2080/';
$CONFIG['settings']['voice.private_accounts_enabled'] = true;
