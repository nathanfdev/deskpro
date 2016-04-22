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

######################################################
# Basic settings for notification system             #
######################################################
# Provide strategies and pusher settings here        #
######################################################
$SETTINGS['notification.settings.pusher_client.appKey'] = '';
$SETTINGS['notification.settings.pusher_client.secret'] = '';
$SETTINGS['notification.settings.pusher_client.appId'] = '';
$SETTINGS['notification.settings.pusher_client.options'] = ['encrypted' => true];

$SETTINGS['notification.settings.strategies'] = [
    'notification.agent_chat.new_message' => [
        'strategy' => 'immediate',
        'delivery' => [
            'pusher',
        ],
    ],
    'notification.agent_chat.mark_message' => [
        'strategy' => 'immediate',
        'delivery' => [
            'pusher',
        ],
    ],
    'notification.agents.update_online' => [
        'strategy' => 'deferred',
        'delivery' => [
            'pusher',
        ],
        'persistance' => 'db'
    ],
];

$SETTINGS['notification.settings.default_strategy'] = [
    'strategy' => 'immediate',
    'delivery' => [
        'pusher',
    ],
];

$SETTINGS['notification.settings.polling_client.polling_interval'] = 500000;

$SETTINGS['audit_log.storage'] = "mongodb";

######################################################
# Disable API response caching                       #
######################################################
$SETTINGS['response.cache.enabled'] = false;
