<?php

$SETTINGS = [];

######################################################
# Disable Portal HTTP Cache                          #
######################################################
# This allows you to completely disable the HTTP     #
# cache on the user portal. This may cause           #
# significant performance implications on the portal.#
######################################################

$SETTINGS['disable_portal_http_cache'] = false;

######################################################
# Disable URL corrections                            #
######################################################
# This disables the auto-redirection that happens    #
# when you try to view the site through a URL that   #
# is not the configured 'helpdesk url.'              #
######################################################

$SETTINGS['disable_url_corrections'] = false;

######################################################
# Disable sending email                              #
######################################################
# This disables sending outgoing email. Email is     #
# still logged in the Outgoing Email Log but it wont #
# actually be sent to anyone. Useful for testing.    #
######################################################

$SETTINGS['disable_outgoing_email'] = false;
