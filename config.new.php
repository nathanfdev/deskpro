<?php

######################################################
# REQUIRED : Your mySQL Database Configuration       #
#                                                    #
# You will need to contact your server administrator #
# for this information. For further help please      #
# contact support@deskpro.com                        #
######################################################

// The database server, often localhost
define('DP_DATABASE_HOST', 'localhost');

// The database username
define('DP_DATABASE_USER', 'root');

// The password for the database user
define('DP_DATABASE_PASSWORD', '');

// The name of the database
define('DP_DATABASE_NAME', 'deskpro');

#########################################################################################################
#########################################################################################################
##################################   ALL SETTINGS BELOW ARE OPTIONAL   ##################################
#########################################################################################################
#########################################################################################################

// leave this
$DP_CONFIG = array();

######################################################
# OPTIONAL : Location of PHP Binary                  #
#                                                    #
# On linux PHP is often located at:                  #
#    /usr/bin/php or /usr/local/bin/php              #
# On windows a typical path maybe                    #
#	 C:\wamp\bin\php\php5.3.8\php.exe                #
######################################################

// $DP_CONFIG['php_path'] = '';

######################################################
# OPTIONAL : Location of Folders                     #
#                                                    #
# You may wish to move the files, logs and backups   #
# folder out of the webroot. If you move the folders #
# you must update the paths below                    #
######################################################

// $DP_CONFIG['folder_files'] = '';
// $DP_CONFIG['folder_backups'] = '';
// $DP_CONFIG['folder_logs'] = '';

######################################################
# OPTIONAL : Memcached                               #
#                                                    #
# Memcached is a server used for cacheing. This can  #
# drastically reduce the load on both your webserver #
# and your database                                  #
######################################################

// $DP_CONFIG['memcached'] = array();
// $DP_CONFIG['memcached']['enabled'] = false;

// $DP_CONFIG['memcached']['servers'][0] = array(
//   'host' => ''
// );

######################################################
# OPTIONAL : DeskPRO Import                          #
#                                                    #
# If you are importing                               #
######################################################

// $DP_CONFIG['import'] = array(
//   'db_host' => '',
//   'db_user' => '',
//   'db_password' => '',
//   'db_name' => ''
//  );

######################################################
# OPTIONAL: Debug Settings                           #
######################################################

// $DP_CONFIG['debug'] = array();
// $DP_CONFIG['debug']['dev'] = true;
///$DP_CONFIG['debug']['raw_assets'] = array();
// $DP_CONFIG['debug']['raw_assets'][] = 'all';

// $DP_CONFIG['debug']['mail'] = array();
// $DP_CONFIG['debug']['mail']['save_to_file'] = true;    // true logs to appfiles/sys/logs/emails
// $DP_CONFIG['debug']['mail']['force_to'] = '';
// $DP_CONFIG['debug']['mail']['disable_send'] = 1;

// $DP_CONFIG['debug']['enable_profiler'] = true;
