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