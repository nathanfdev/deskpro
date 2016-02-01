<?php

$ENV_CONFIG = [];

######################################################
# Set file umask                                     #
######################################################
# If you have NOT configured 'chmod +a' or ACL's on  #
# your system, you should keep this line set to 0000 #
# to make sure files in var/ are writable by both    #
# the web server and the CLI user(s).                #
######################################################
# 0000: Files created will be world read/writable    #
#       (aka "chmod 0777")                           #
#       This is the easiest option, but least secure #
#       because any user on the system can write.    #
#                                                    #
# 0002: Files created will be read/writable by the   #
#       file owner and group (aka "chmod 0775").     #
#       Use this if your web server and CLI user     #
#       are part of the same group.                  #
######################################################

$ENV_CONFIG['set_umask'] = 0000;

######################################################
# DeskPRO runtime environment                        #
######################################################
# This can be: prod, dev or test                     #
######################################################

$ENV_CONFIG['environment'] = 'prod';

######################################################
# Enable DEBUG mode                                  #
######################################################
# This enables various debug options at              #
# the cost of performance. If the environemnt is dev #
# then this is always enabled.                       #
######################################################

$ENV_CONFIG['debug_mode'] = false;
