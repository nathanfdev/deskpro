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

// $DP_CONFIG['php_path'] = '';

######################################################
# Location of mysqldump                              #
#                                                    #
# You need to specify this path if the system        #
# cannot detect it automatically.                    #
#                                                    #
# On Linux PHP is often located at:                  #
#    /usr/bin/mysqldump or /usr/local/bin/mysqldump  #
# On Windows a typical path may be                   #
#	 C:\wamp\bin\php\php5.3.8\php.exe                #
######################################################

// $DP_CONFIG['mysqldump_path'] = '';

######################################################
# OPTIONAL : Location of Directories                 #
#                                                    #
# You may wish to move the files, logs and backups   #
# directories out of the webroot. If you move them,  #
# you must update the paths below.                   #
######################################################

// $DP_CONFIG['dir_files'] = '';
// $DP_CONFIG['dir_backups'] = '';
// $DP_CONFIG['dir_logs'] = '';

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
//   'db_name' => '',
//   'store_attachment_files' => false,
//   'existing_attachment_files' => ''
//  );

