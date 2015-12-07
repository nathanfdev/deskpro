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
    'portal.emails.validate-email' => 'Please confirm that this email address is yours by clicking the link below.',

    // older clones:
    'portal.emails.auto-close'                      => 'Your ticket "{{ticket.subject}}" will be closed automatically because you have not updated it lately. If you do not want your ticket to be closed, you can add a new reply so our agents can help you further.',
    'portal.emails.chat_transcript'                 => 'Thank you for chatting with us. Here is your chat transcript.',
    'portal.emails.comment_approved'                => 'Your comment has been published.',
    'portal.emails.comment_deleted'                 => 'Your comment was used to improve the page.',
    'portal.emails.comment_thank-you'               => 'Thank you for your comment on {{link}}.',
    'portal.emails.comment_validate-email'          => 'Please validate your email address online by clicking the following link:',
    'portal.emails.do-not-reply'                    => 'Please do not reply to this email. This is a machine-generated message and replies will not be read by our staff.',
    'portal.emails.email-too-big'                   => 'Your email "{{subject}}" was not accepted because it is too big. The maximum email size this helpdesk accepts is {{max_size}}. Remove any attachments and try again.',
    'portal.emails.feedback_agent-validation'       => 'Note that your feedback will not appear publicly on our website until one of our agents manually reviews it.',
    'portal.emails.feedback_approved'               => 'We are emailing to let you know that your feedback as approved and is now publicly visible on our website.',
    'portal.emails.feedback_closed'                 => 'Your submitted feedback "{{title}}" was closed: {{status}}',
    'portal.emails.feedback_disapproved'            => 'We are emailing to let you know that unfortunately your feedback has been declined and will not appear publicly on our website.',
    'portal.emails.feedback_disapproved-reason'     => 'We are emailing to let you know that unfortunately your feedback has been declined and will not appear publicly on our website. {{name}} gave the following reason:',
    'portal.emails.feedback_new-comment'            => '{{name}} posted a new comment:',
    'portal.emails.remind_unresolved_final_subject' => 'REMINDER: {{ ticket.subject }}',
    'portal.emails.remind_unresolved_subject'       => 'REMINDER: {{ ticket.subject }}',
    'portal.emails.feedback_published'              => 'Your submitted feedback "{{title}}" has been validated and will now appear on our website.',
    'portal.emails.feedback_thank-you'              => 'Thank you for submitting your feedback "{{title}}"',
    'portal.emails.feedback_updated'                => 'Your submitted feedback "{{title}}" has been updated. It is now filed under the new status "{{status}}"',
    'portal.emails.feedback_validate-email'         => 'Please validate your email address online by clicking the following link:',
    'portal.emails.feedback_votes'                  => 'Your feedback currently has <strong>1</strong> vote. View your feedback online at:|Your feedback currently has <strong>{{count}}</strong> votes. View your feedback online at:',
    'portal.emails.greeting'                        => 'Dear {{to_name}},',
    'portal.emails.kb-explain'                      => 'You are receiving this email because you are subscribed to our knowledgebase at <a href="{{ deskpro_url }}">{{ deskpro_url }}</a>.<br />Do not want these emails anymore? <a href="{{ unsubscribe_url }}">Click here to unsubscribe from all knowledgebase notifications.</a>',
    'portal.emails.kb-new-articles'                 => '1 New Article|{{count}} New Articles',
    'portal.emails.kb-updated-articles'             => '1 Updated Article|{{count}} Updated Articles',
    'portal.emails.news-explain'                    => 'You are receiving this email because you are subscribed to our news at <a href="{{ deskpro_url }}">{{ deskpro_url }}</a>.<br />Do not want these emails anymore? <a href="{{ unsubscribe_url }}">Click here to unsubscribe from all news notifications.</a>',
    'portal.emails.feedback-explain'                => 'You are receiving this email because you are subscribed to our news at <a href="{{ deskpro_url }}">{{ deskpro_url }}</a>.<br />Do not want these emails anymore? <a href="{{ unsubscribe_url }}">Click here to unsubscribe from all feedback notifications.</a>',
    'portal.emails.news-new-articles'               => '1 New News Article|{{count}} New News Articles',
    'portal.emails.news-updated-articles'           => '1 Updated News Article|{{count}} Updated News Articles',
    'portal.emails.feedback-updated-items'          => '1 Updated Feedback Item|{{count}} Updated Feedback Items',
    'portal.emails.downloads-explain'               => 'You are receiving this email because you are subscribed to our downloads at <a href="{{ deskpro_url }}">{{ deskpro_url }}</a>.<br />Do not want these emails anymore? <a href="{{ unsubscribe_url }}">Click here to unsubscribe from all downloads notifications.</a>',
    'portal.emails.download-new-downloads'          => '1 New Download|{{count}} New Downloads',
    'portal.emails.download-updated-downloads'      => '1 Updated Download|{{count}} Updated Downloads',
    'portal.emails.label_view-online'               => 'View Online',
    'portal.emails.message-clipped'                 => '(Message has been clipped)',
    'portal.emails.password_agent-reset'            => 'An agent has reset your account password. You can log in with these credentials',
    'portal.emails.password_reset'                  => 'You requested a password reset. To reset your password, click on the link below:',
    'portal.emails.password_set'                    => 'To set your password, click on the link below:',
    'portal.emails.profile_email-new-confirm'       => 'To finish adding this secondary email address to your account, simply click on the following validation link',
    'portal.emails.profile_email-primary-confirm'   => 'To verify your primary email address, simply click on the following validation link',
    'portal.emails.profile_email_link-validate'     => 'Simply click on the following link to validate to your email address',
    'portal.emails.register-agent-validation'       => 'Note: Before your account is fully active, our agents must manually validate your account. Tickets and other content you submit will be held in a validation queue until an agent validates your accounts.',
    'portal.emails.register-confirm'                => 'Thank you for registering an account.<br /><br />Before you will be able to use your account, you must click on the following link to validate your email address',
    'portal.emails.register-welcome'                => 'Thank you for registering. You can now log in using your email address {{to_email}} on our helpdesk:',
    'portal.emails.register-welcome-byagent'        => 'A new account has been created for you. You can now log in using your email address {{to_email}} on our helpdesk:',
    'portal.emails.register-password'               => 'Your initial password is: {{password}}',
    'portal.emails.registration_closed'             => 'Dear {{name}},<br /><br />New tickets are only accepted from existing helpdesk members. If you already have an account, please email us again from your registered email address.',
    'portal.emails.reject_resolved'                 => 'Your reply was not accepted because your ticket has already been marked as resolved. Our agents will not read or reply to this message.',
    'portal.emails.reject_resolved-new'             => 'If you would like to create a brand new ticket you can send a new email to <a href="mailto:{{email_to}}">{{email_to}}</a> or you can submit our online form at:<br /><a href="{{link}}">{{link}}</a>',
    'portal.emails.reject_resolved-newemail'        => 'If you would like to create a brand new ticket you can send a new email to <a href="mailto:{{email_to}}">{{email_to}}</a>',
    'portal.emails.remind_unresolved'               => 'This is a follow-up message to remind you that you still have an open ticket with {{ helpdesk_name }}.<br />If you don\'t need any further help with this issue, please click the link below to let us know:<br /><a href="{{ resolve_url }}">{{ resolve_url }}</a><br />If you still need help, simply reply to this email.',
    'portal.emails.remind_unresolved_final'         => 'We haven\'t heard from you recently about this ticket.<br />If you still need help, simply reply to this email.<br />If you don\'t need any further help with this issue, please click the link below to let us know:<br /><a href="{{ resolve_url }}">{{ resolve_url }}</a>',
    'portal.emails.ticket_access_ticket_online'     => 'View and manage this ticket online:',
    'portal.emails.ticket_cc-new'                   => 'You have been included in a ticket started by {{name}}.',
    'portal.emails.ticket_flood'                    => 'You recently sent an email to our helpdesk. Our automated system has detected that you have sent many messages in rapid succession that were most likely automated.<br /><br />To protect against an auto-responder loop our helpdesk will not send any more automatic notification emails to you.',
    'portal.emails.ticket_message_title'            => 'On {{date}} at {{time}}, {{author}} wrote:',
    'portal.emails.ticket_no-autoresponse'          => 'Warning: Confirmation emails turned off',
    'portal.emails.ticket_rate-negative'            => 'No',
    'portal.emails.ticket_rate-neutral'             => 'It was OK',
    'portal.emails.ticket_rate-positive'            => 'Yes',
    'portal.emails.ticket_rate-request'             => 'How would you rate the support you received on your ticket "{{ticket.subject}}"?',
    'portal.emails.ticket_rate-question'            => 'Was this message helpful?',
    'portal.emails.ticket_received'                 => 'Your ticket has been received. One of our agents will reply to you shortly.',
    'portal.emails.ticket_reply-confirm'            => 'Thank you for your reply. One of our agents will reply to you shortly.',
    'portal.emails.ticket_validate'                 => 'Thank you for contacting us.<br /><br />Before our agents will read and reply to your message, you must validate your email address.',
    'portal.emails.tickets_ommitted'                => '1 message has been omitted|{{count}} messages have been omitted',
    'portal.emails.view_full_history_online'        => 'View full ticket online',
    'portal.emails.ratelimit_explain'               => 'You have sent {{num_messagess}} messages within {{time_limit}}. To prevent abuse, we have temporarily banned your email address for {{time_lock}}. After {{date_lock_end}}, this temporary ban will be automatically deactivated and you will be able to send emails again.',
    'portal.emails.ratelimit_submit-online'         => 'If this is a mistake and you need to contact us immediately, you can bypass this email filter and submit a ticket online:',
    'portal.emails.new-ticket-guest-link'           => 'Thank you for contacting us. You may view the status of your ticket online at this address:',
);
