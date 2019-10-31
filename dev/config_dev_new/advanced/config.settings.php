<?php

$SETTINGS = [];

######################################################
# Disable Portal HTTP Cache
######################################################

$SETTINGS['disable_portal_http_cache'] = true;

######################################################
# Disable URL corrections
######################################################

$SETTINGS['disable_url_corrections'] = true;

######################################################
# Disable sending email
######################################################

$SETTINGS['disable_outgoing_email'] = true;

######################################################
# Enable all experimental features
######################################################

$SETTINGS['enable_experimental'] = ['all' => true];

######################################################
# Deskpro Stack Services
######################################################
# The stack services are things we run in the cloud.
# The local Deskpro instance calls these services
# with an access token and auth token, usually
# set up via a cloud site or license. During
# dev you may hard-code these settings here for
# easier setup.
######################################################

$SETTINGS['dpss.access_token'] = '';
$SETTINGS['dpss.auth_token'] = '';
$SETTINGS['voice.private_accounts_enabled'] = true;
