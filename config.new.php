<?php

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#                Welcome to DeskPRO
#             http://support.deskpro.com
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

######################################################
# Your mySQL Database Configuration                  #
######################################################

// The database server. If you are using windows and your
// mysql server is on the same machine; it is important not
// to specify localhost, specify 127.0.0.1 instead.
define('DP_DATABASE_HOST', 'localhost');

// The database username
define('DP_DATABASE_USER', 'root');

// The password for the database user
define('DP_DATABASE_PASSWORD', '');

// The name of the database
define('DP_DATABASE_NAME', 'deskpro');

// Specify an email address to receive reports of any
// database problems that prevent DeskPRO from working
define('DP_TECHNICAL_EMAIL', '');









# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# The mySQL database settings are the only required
# settings needed to install DeskPRO. You only need
# to change the settings that follow if the DeskPRO
# software, a knowledgebase article or a member
# of DeskPRO's customer service team advise you
# to do so.
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

// do not edit the next line
$DP_CONFIG = array('debug' => array(), 'cache' => array());

# ~~~~~~~~~~~~~~~~~~~~  PATHS ~~~~~~~~~~~~~~~~~~~~~~~~

######################################################
# Location of PHP Command Line Interface CLI         #
#                                                    #
# You need to specify this path if the system        #
# cannot detect it automatically.                    #
#                                                    #
# On Linux PHP is often located at:                  #
#    /usr/bin/php or /usr/local/bin/php              #
#                                                    #
# On Windows PHP maybe found at                      #
#	 C:\Program Files\php\php-win.exe                #
#                                                    #
# If you are using windows please ensure you use the #
# win-php.exe version of PHP and not the php.exe     #
# version. This prevents a command line window being #
# generated everytime PHP is run.                    #
#                                                    #
# Please note that it must be the CLI version of PHP #
# and not, for example, a cgi-fcgi binary. You can   #
# determine the PHP type by typing /path/to/php -v   #
# on the command line and looking for the string     #
# such as the one below. The cli part is required    #
#                                                    #
# PHP 5.3.10 (cli) (built: Mar 27 2012 1239:38)      #
######################################################

$DP_CONFIG['php_path'] = '';

######################################################
# Location of mysqldump                              #
#                                                    #
# mysqldump is a command line tool used to generate  #
# backups of your mysql database                     #
#                                                    #
# You need to specify this path if the system        #
# cannot detect it automatically.                    #
#                                                    #
# On Linux mysqlump is often located at:             #
#    /usr/bin/mysqldump or /usr/local/bin/mysqldump  #
#                                                    #
# On Windows mysqldump may be found at:              #
#    C:\Program Files\mysql\bin\mysqldump.exe        #
######################################################

$DP_CONFIG['mysqldump_path'] = '';

######################################################
# Location of mysql                                  #
#                                                    #
# mysql is the command line version of the mysql     #
# client                                             #
#                                                    #
# You need to specify this path if the system        #
# cannot detect it automatically.                    #
#                                                    #
# On Linux mysql is often located at:                #
#    /usr/bin/mysql or /usr/local/bin/mysql          #
#                                                    #
# On Windows mysql maybe be found at:                #
#    C:\Program Files\mysql\bin\mysql.exe            #
######################################################

$DP_CONFIG['mysql_path'] = '';

######################################################
# Location of the Data directory                     #
#                                                    #
# You may wish change the location of the data       #
# directory. There are some security benefits from   #
# having this directory outside of the webroot. If   #
# you do move the folder, please remember to ensure  #
# it remains writable.                               #
#                                                    #
# You should specify the full path to the data       #
# directory.                                         #
#                                                    #
# You should be regularly backing up the data        #
# directory.                                         #
######################################################

$DP_CONFIG['dir_data'] = '';

# ~~~~~~~~~~~~~~~~ DESKPRO IMPORT ~~~~~~~~~~~~~~~~~~~~

######################################################
# DeskPRO Import Settings                            #
#                                                    #
# Enter the database details of your current         #
# DeskPRO v1, DeskPRO v2 or DeskPRO v3 database if   #
# you wish to import their data when installing      #
# DeskPRO v4.                                        #
#                                                    #
# If you are upgrading from one version of           #
# DeskPRO v4 you should not do anything here. That   #
# upgrade is controlled via the Admin interface.     #
#                                                    #
# The importer system will move your attachments to  #
# the filesystem, storing the files in /data/files   #
# It is recommended that you store files this way    #
# however if you wish for files to remain stored in  #
# the database you should change the line:           #
#                                                    #
# 'store_attachments_files' => true,                 #
#           TO                                       #
# 'store_attachments_files' => false;                #
#                                                    #
# You can change the location of the data directory  #
# which contains the files directory by setting the  #
# Data directory setting above.                      #
#                                                    #
# IMPORTANT:                                         #
#                                                    #
# Please ensure you read the README.txt file for     #
# instructions on how to run an import. You need to  #
# run import.php from the command line and not       #
# install DeskPRO using the browser.                 #
######################################################

$DP_CONFIG['import'] = array(
  'db_host' => 'localhost',
  'db_user' => 'root',
  'db_password' => '',
  'db_name' => 'deskpro',
  'store_attachment_files' => true,
  'existing_attachment_files' => ''
);

######################################################
# OPTIONAL: Trust proxy data                         #
#                                                    #
# You should enable this option if you want to trust #
# proxy data passed in request headers. Typically    #
# you only need to do this if you are hosting        #
# DeskPRO behind a reverse proxy.                    #
######################################################

$DP_CONFIG['trust_proxy_data'] = false;

# ~~~~~~~~~~~~~~~~ DEBUG & LOGS ~~~~~~~~~~~~~~~~~~~~~~

######################################################
# OPTIONAL : Disable URL corrections                 #
#                                                    #
# This disables the auto-redirection that happens    #
# when you try to view the site through a URL that   #
# is not the configured 'helpdesk url.'              #
######################################################

$DP_CONFIG['disable_url_corrections'] = false;

######################################################
# OPTIONAL : Enable debug call trace                 #
#                                                    #
# Sometimes a support agent may ask you to enable    #
# this option to help debug a problem                #
######################################################

$DP_CONFIG['debug']['enable_debug_trace'] = false;
$DP_CONFIG['debug']['enable_debug_trace_keep'] = false;

######################################################
# OPTIONAL : Slow Page Logs                          #
#                                                    #
# Sometimes a support agent may ask you to enable    #
# these options to help debug a problem              #
######################################################

$DP_CONFIG['debug']['enable_slow_page_log'] = false;
$DP_CONFIG['debug']['enable_slow_page_log_minquerytime'] = 0.01;

######################################################
# OPTIONAL : Usersource Log                          #
#                                                    #
# Enables log for external usersource adapters for   #
# troubleshooting.                                   #
######################################################

$DP_CONFIG['debug']['enable_usersource_log'] = false;

######################################################
# OPTIONAL : Mail Debug                              #
#                                                    #
# Sometimes a support agent may ask you to enable    #
# these options to help debug a problem              #
######################################################

$DP_CONFIG['debug']['mail'] = array();
$DP_CONFIG['debug']['mail']['enable_mail_log'] = false;
$DP_CONFIG['debug']['mail']['save_to_file'] = false;
$DP_CONFIG['debug']['mail']['disable_send'] = false;
$DP_CONFIG['debug']['mail']['force_to'] = '';

######################################################
# OPTIONAL : Caching                                 #
#                                                    #
# Configure how and whether user interface pages are #
# cached for increased performance.                  #
######################################################

$DP_CONFIG['cache']['page_cache'] = array();
$DP_CONFIG['cache']['page_cache']['enable'] = true;
$DP_CONFIG['cache']['page_cache']['ttl'] = 900;
$DP_CONFIG['cache']['page_cache']['max_size'] = 10000000;
$DP_CONFIG['cache']['page_cache']['enable_hit_log'] = false;
$DP_CONFIG['cache']['page_cache']['hit_log_file'] = '';

######################################################
# OPTIONAL : Read Only Database                      #
#                                                    #
# Configure whether a special database is used for   #
# particularly exprensive read queries.              #
######################################################

$DP_CONFIG['db_read'] = array();
$DP_CONFIG['db_read']['host'] = '';
$DP_CONFIG['db_read']['user'] = '';
$DP_CONFIG['db_read']['password'] = '';
$DP_CONFIG['db_read']['dbname'] = '';