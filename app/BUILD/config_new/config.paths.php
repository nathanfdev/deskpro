<?php

$PATHS_CONFIG = [];

######################################################
# Location of PHP CLI Binary                         #
######################################################
#                                                    #
# You need to specify this path if the system        #
# cannot detect it automatically.                    #
#                                                    #
# On Linux PHP is often located at:                  #
#    /usr/bin/php or /usr/local/bin/php              #
#                                                    #
# On Windows, PHP if often found at:                 #
#    C:\Program Files\PHP\v7.0\php.exe               #
#    or C:\Program Files (x86)\PHP\v7.0\php.exe      #
#                                                    #
# Please note that it must be the CLI version of PHP #
# and not, for example, a cgi-fcgi binary. You can   #
# determine the PHP type by typing /path/to/php -v   #
# on the command line and looking for the string     #
# such as the one below. The cli part is required    #
#                                                    #
# PHP 7.0.1 (cli) (built: Oct  4 2016 09:23:10)      #
######################################################

$PATHS_CONFIG['php_path'] = '';

######################################################
# Location of the mysqldump Binary                   #
######################################################
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

$PATHS_CONFIG['mysqldump_path'] = '';

######################################################
# Location of the mysql Binary                       #
######################################################
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

$PATHS_CONFIG['mysql_path'] = '';

######################################################
# Location of DeskPRO data directories               #
######################################################
# Override any of these values to change the paths   #
# to DeskPRO's system directories.                   #
######################################################
# Do NOT edit/change these unless you are sure you   #
# want to change these paths from the default.       #
# It is almost never necessary to change these.      #
######################################################

$PATHS_CONFIG['dp_paths'] = [
    // Optionally specify a specific "user directory" where
    // all instance files are written to. This is useful if you want to completely
    // separate DeskPRO application app files from any instance-specific files that get written.
    //
    // Setting this directory will change all others by default:
    //   - $user_dir/attachments will be used for attachments
    //   - $user_dir/backups will be used for backups
    //   - $user_dir/var will be used for var (which includes sub-dirs for logs, tmp, etc)
    'user_dir' => null,

    // Change the path to the 'attachments' directory.
    // This is where uploads, ticket attachments, images,
    // and other binary data is stored.
    // Default: /path/to/deskpro/attachments
    'attachments' => null,

    // Change the path to the 'backups' directory.
    // This is where DeskPRO will store database backups
    // that are made before an automatic upgrade.
    // Default: /path/to/deskpro/backups
    'backups' => null,

    // Change the path to the var/logs directory.
    // This is where DeskPRO writes all log files to.
    // Default: /path/to/deskpro/var/logs
    'logs' => null,

    // Change the path to the var/tmp directory.
    // This is where DeskPRO writes temporary files to.
    // Default: /path/to/deskpro/var/tmp
    'tmp' => null,
];
