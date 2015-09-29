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

return array(
    'adm.email_status.error_server_error'         => 'A server error occurred while processing the message.',
    'adm.email_status.error_from_missing'         => 'The email was missing a From email address.',
    'adm.email_status.error_from_invalid'         => 'The From email address was invalid.',
    'adm.email_status.error_from_gateway_address' => 'The email was sent from a known helpdesk email address. DeskPRO never processes emails from addresses you have configured as ticket accounts. This prevents loops (i.e., DeskPRO emailing itself).',
    'adm.email_status.error_from_banned'          => 'The email was rejected because it was from a banned email address.',
    'adm.email_status.error_from_disabled_user'   => 'The email was rejected because it was from a disabled user.',
    'adm.email_status.error_subject_missing'      => 'The email was rejected because the subject was missing.',
    'adm.email_status.error_message_missing'      => 'The email was rejected because the message was missing.',
    'adm.email_status.error_message_too_big'      => 'The email was rejected because the message was too big.',
    'adm.email_status.error_empty'                => 'The email was rejected because it was empty.',
    'adm.email_status.error_duplicate_message'    => 'The email was rejected because an exact duplicate has already been processed.',
    'adm.email_status.error_autoresponder'        => 'The email was rejected because it was sent by an autoresponder bot.',
    'adm.email_status.error_spam'                 => 'The email was rejected because it is spam.',
    'adm.email_status.error_require_reg'          => 'The email was rejected because the helpdesk requires registration but the user is not registered yet.',
    'adm.email_status.error_obj_closed'           => 'The email was rejected because it is in reply to a closed ticket.',
    'adm.email_status.error_obj_deleted'          => 'The email was rejected because it is in reply to a deleted ticket.',
    'adm.email_status.error_obj_unknown'          => 'The email was rejected because the ticket could not be determined.',
    'adm.email_status.error_auth_invalid'         => 'The email was rejected because it did not include valid auth codes. (These are hidden codes included in all email notifications.)',
    'adm.email_status.error_auth_missing'         => 'The email was rejected because it was missing auth codes. (These are hidden codes included in all email notifications.)',
    'adm.email_status.error_deskpro_email'        => 'deskpro_email',
    'adm.email_status.error_perm_insufficient'    => 'The email was rejected because the user did not have sufficient permissions to use the email address.',
    'adm.email_status.error_invalid_fwd'          => 'The email was rejected because the forwarded message could not be parsed.',
    'adm.email_status.error_invalid_fwd_email'    => 'The email was rejected because the email address of the user in the forwarded message could not be parsed.',
    'adm.email_status.error_missing_marker'       => 'The email was rejected because it is missing the marker line ("reply above this line").',
    'adm.email_status.error_agent_bounce'         => 'The email was rejected because it was detected as a bounced message.',
    'adm.email_status.error_date_limit'           => 'The email was rejected because of flood check limit.',
    'adm.email_status.error_invalid_address'      => 'The email was rejected because it did not match any known email account.',
    'adm.email_status.error_timeout'              => 'The system has been processing the message for too long. This usually means an error happened and the message was aborted.',
);
