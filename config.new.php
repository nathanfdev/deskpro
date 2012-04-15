<?php

######################################################
#                Welcome to DeskPRO!                 #
#             http://support.deskpro.com             #
######################################################


######################################################
# REQUIRED : Your mySQL Database Configuration       #
######################################################

// The database server, often localhost
define('DP_DATABASE_HOST', 'localhost');

// The database username
define('DP_DATABASE_USER', 'root');

// The password for the database user
define('DP_DATABASE_PASSWORD', '');

// The name of the database
define('DP_DATABASE_NAME', 'deskpro');

// A regularly monitoreed email address where technical
// issues, such as server errors, will be reported to
define('DP_TECHNICAL_EMAIL', '');

######################################################
# OPTIONAL : ALL SETTINGS BELOW ARE OPTIONAL         #
######################################################

// leave this
$DP_CONFIG = array();

######################################################
# Location of PHP Binary                             #
#                                                    #
# You need to specify this path if the system        #
# cannot detect it automatically.                    #
#                                                    #
# On Linux PHP is often located at:                  #
#    /usr/bin/php or /usr/local/bin/php              #
# On Windows a typical path may be                   #
#	 C:\wamp\bin\php\php5.3.8\php.exe                #
######################################################

$DP_CONFIG['php_path'] = '';

######################################################
# Location of mysqldump                              #
#                                                    #
# You need to specify this path if the system        #
# cannot detect it automatically.                    #
#                                                    #
# On Linux mysqlump is often located at:             #
#    /usr/bin/mysqldump or /usr/local/bin/mysqldump  #
######################################################

$DP_CONFIG['mysqldump_path'] = '';

######################################################
# Location of mysql                                  #
#                                                    #
# You need to specify this path if the system        #
# cannot detect it automatically.                    #
#                                                    #
# On Linux mysql is often located at:                #
#    /usr/bin/mysql or /usr/local/bin/mysql          #
######################################################

$DP_CONFIG['mysql_path'] = '';

######################################################
# OPTIONAL : DeskPRO Import                          #
#                                                    #
# Enter the database details of your current deskpro #
# database to import the data to DeskPRO v4          #
#                                                    #
# If you would like to move attachments to the file  #
# system (recommended) change the value from false   #
# to true for store_attachment_files                 #
######################################################

$DP_CONFIG['import'] = array(
  'db_host' => 'localhost',
  'db_user' => 'root',
  'db_password' => '',
  'db_name' => 'deskpro',
  'store_attachment_files' => false,
  'existing_attachment_files' => ''
);

######################################################
# OPTIONAL : Location of Directories                 #
#                                                    #
# You may wish to move the files, logs and backups   #
# directories out of the webroot. If you move them,  #
# you must update the paths below.                   #
######################################################

$DP_CONFIG['dir_files'] = '';
$DP_CONFIG['dir_backups'] = '';
$DP_CONFIG['dir_logs'] = '';

######################################################
# OPTIONAL : Enable debug call trace                 #
#                                                    #
# Sometimes a support agent may ask you to enable    #
# this option to help debug a problem                #
######################################################

$DP_CONFIG['enable_debug_trace'] = false;

######################################################
# OPTIONAL : Slow Page Logs                          #
#                                                    #
# Sometimes a support agent may ask you to enable    #
# these options to help debug a problem              #
######################################################

$DP_CONFIG['enable_slow_page_log'] = false;
$DP_CONFIG['enable_slow_page_log_minquerytime'] = false;

######################################################
# OPTIONAL : Mail Debug                              #
#                                                    #
# Sometimes a support agent may ask you to enable    #
# these options to help debug a problem              #
######################################################

$DP_CONFIG['debug']['mail'] = array();
$DP_CONFIG['debug']['mail']['save_to_file'] = false;
$DP_CONFIG['debug']['mail']['disable_send'] = false;
$DP_CONFIG['debug']['mail']['force_to'] = '';
