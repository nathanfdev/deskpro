<?php

$DB_CONFIG     = [];

######################################################
# DeskPRO Database                                   #
######################################################
# Enter the MySQL database details for the database  #
# you want to use with DeskPRO.                      #
#                                                    #
# Refer to the README for more information.          #
######################################################

$DB_CONFIG['host']     = 'localhost';
$DB_CONFIG['user']     = 'root';
$DB_CONFIG['password'] = '';
$DB_CONFIG['dbname']   = 'deskpro';


######################################################
# DeskPRO System Database                            #
######################################################
# Enter details for the database you want to use     #
# for DeskPRO system needs.                          #
#                                                    #
# We recommend using a separate DB for the system    #
# information, but it's optional. If you leave the   #
# below config commented out, system information     #
# will be stored in the main database.               #
######################################################

//$DB_CONFIG['system']['host']     = 'localhost';
//$DB_CONFIG['system']['user']     = 'root';
//$DB_CONFIG['system']['password'] = '';
//$DB_CONFIG['system']['dbname']   = 'deskpro_sys';
