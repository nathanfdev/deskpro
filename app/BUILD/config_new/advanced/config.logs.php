<?php

$LOGS_CONFIG = [];

######################################################
# General Application Log                            #
######################################################
# The application log includes a lot of diagnostic   #
# information about any request served by the system.#
#                                                    #
# Change these values to change how much data to add #
# to the log:                                        #
#                                                    #
# - log_level: Which level of logging to save        #
# - log_leve_threshold: When to start saving log     #
#   messages. If no log message is emitted at this   #
#   level or higher, no logs will be saved.          #
#                                                    #
# Default is info/error. This means if an error      #
# happens, the system will save all info messages    #
# (or higher) for the duration of the request.       #
#                                                    #
# Log levels: debug, info, notice, warning, error    #
#             critical, alert, emergency             #
######################################################

$LOGS_CONFIG['log_level']           = 'info';
$LOGS_CONFIG['log_level_threshold'] = 'error';

######################################################
# OPTIONAL : DB Query Log                            #
######################################################
# Enables DB query logs to the general app log file. #
######################################################

$LOGS_CONFIG['log_db_queries'] = false;