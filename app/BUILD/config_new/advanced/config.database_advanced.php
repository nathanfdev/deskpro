<?php

$DB_CONFIG                 = [];
$DB_CONFIG['read']         = [];
$DB_CONFIG['read_reports'] = [];
$DB_CONFIG['read_search']  = [];

######################################################
# Generic READ database                              #
######################################################
# Define one or more databases for READ queries.     #
######################################################
# This database is used for any READ context when    #
# a more specific (defined below) is not defined.    #
######################################################

//$DB_CONFIG['read'][0]['host']     = 'localhost';
//$DB_CONFIG['read'][0]['user']     = 'deskpro';
//$DB_CONFIG['read'][0]['password'] = '';
//$DB_CONFIG['read'][0]['dbname']   = 'deskpro_read';


######################################################
# Reporting Database                                 #
######################################################
# Define one or more databases used for REPORTING    #
######################################################

//$DB_CONFIG['read_reports'][0]['host']     = 'localhost';
//$DB_CONFIG['read_reports'][0]['user']     = 'deskpro';
//$DB_CONFIG['read_reports'][0]['password'] = '';
//$DB_CONFIG['read_reports'][0]['dbname']   = 'deskpro_read';


######################################################
# Search/filtering Database                          #
######################################################
# Define one or more databases used for FILTERING    #
######################################################

//$DB_CONFIG['read_search'][0]['host']     = 'localhost';
//$DB_CONFIG['read_search'][0]['user']     = 'deskpro';
//$DB_CONFIG['read_search'][0]['password'] = '';
//$DB_CONFIG['read_search'][0]['dbname']   = 'deskpro_read';

######################################################
# System Incidents Database                          #
######################################################
# Define a separate db for system errors/incidents   #
######################################################

//$DB_CONFIG['system']['host']     = 'localhost';
//$DB_CONFIG['system']['user']     = 'root';
//$DB_CONFIG['system']['password'] = '';
//$DB_CONFIG['system']['dbname']   = 'deskpro_sys';

######################################################
# Audit Log Database                                 #
######################################################
# Define a separate db for audit logging             #
######################################################

//$DB_CONFIG['audit']['host']     = 'localhost';
//$DB_CONFIG['audit']['user']     = 'root';
//$DB_CONFIG['audit']['password'] = '';
//$DB_CONFIG['audit']['dbname']   = 'deskpro_audit';
