<?php

$DB_CONFIG = [];

$DB_CONFIG['host']     = 'localhost';
$DB_CONFIG['user']     = 'root';
$DB_CONFIG['password'] = '';
$DB_CONFIG['dbname']   = 'deskpro';

// Or you can edit the file DB_NAME.txt with the name of your db
// and use this loader. This makes it easier to create tools (e.g., bin/copy-db).
//require_once __DIR__.'/advanced/lib.php';
//$DB_CONFIG['dbname'] = dev_config_read_file('DB_NAME.txt');

//$DB_CONFIG['system']['host']     = 'localhost';
//$DB_CONFIG['system']['user']     = 'root';
//$DB_CONFIG['system']['password'] = '';
//$DB_CONFIG['system']['dbname']   = 'deskpro_sys';

//$DB_CONFIG['audit']['host']     = 'localhost';
//$DB_CONFIG['audit']['user']     = 'root';
//$DB_CONFIG['audit']['password'] = '';
//$DB_CONFIG['audit']['dbname']   = 'deskpro_audit';