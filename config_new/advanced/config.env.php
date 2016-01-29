<?php

$ENV_CONFIG = [];

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
