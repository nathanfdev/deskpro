<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
