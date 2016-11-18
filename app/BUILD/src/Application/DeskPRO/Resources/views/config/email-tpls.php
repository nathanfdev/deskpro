<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

return [
    //###################################################################################################################
    // Layout
    //###################################################################################################################
    [
        'typeId'     => 'layout',
        'groupId'    => 'top',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_common:email-header.html.twig',
        'type'       => 'template',
    ],
    [
        'typeId'     => 'layout',
        'groupId'    => 'top',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_common:email-footer.html.twig',
        'type'       => 'template',
    ],
    [
        'typeId'     => 'layout',
        'groupId'    => 'top',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_common:email-custom-css.css.twig',
        'type'       => 'template',
    ],
    [
        'typeId'     => 'layout',
        'groupId'    => 'top',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_common:email-css.css.twig',
        'type'       => 'template',
    ],

    //###################################################################################################################
    // Agent
    //###################################################################################################################

    //--------------------
    // Tickets
    //--------------------
    [
        'typeId'      => 'agent',
        'groupId'     => 'tickets',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_agent:ticket-new.html.twig',
        'newTemplate' => 'SendmailBundle:emails_agent:ticket_new.html.twig',
        'viewModel'   => 'AgentTicketNew',
    ],
    [
        'typeId'      => 'agent',
        'groupId'     => 'tickets',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_agent:ticket-update.html.twig',
        'newTemplate' => 'SendmailBundle:emails_agent:ticket_update.html.twig',
        'viewModel'   => 'AgentTicketUpdate',
    ],
    [
        'typeId'      => 'agent',
        'groupId'     => 'tickets',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_agent:ticket-reply.html.twig',
        'newTemplate' => 'SendmailBundle:emails_agent:ticket_reply.html.twig',
        'viewModel'   => 'AgentTicketReply',
    ],
    [
        'typeId'     => 'agent',
        'groupId'    => 'tickets',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_common:ticket-props-table.html.twig',
        'type'       => 'template',
    ],
    [
        'typeId'     => 'agent',
        'groupId'    => 'tickets',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_common:ticket-fwd-out-header.html.twig',
        'type'       => 'template',
    ],

    //--------------------
    // General
    //--------------------
    [
        'typeId'      => 'agent',
        'groupId'     => 'general',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_agent:new-agent-chat-message.html.twig',
        'newTemplate' => 'SendmailBundle:emails_agent:new_agent_chat_message.html.twig',
        'viewModel'   => 'AgentNewChatMessage',
    ],
    [
        'typeId'      => 'agent',
        'groupId'     => 'general',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_agent:new-comment.html.twig',
        'newTemplate' => 'SendmailBundle:emails_agent:new_comment.html.twig',
        'viewModel'   => 'AgentNewComment',
    ],
    [
        'typeId'      => 'agent',
        'groupId'     => 'general',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_agent:new-feedback.html.twig',
        'newTemplate' => 'SendmailBundle:emails_agent:new_feedback.html.twig',
        'viewModel'   => 'AgentNewFeedback',
    ],
    [
        'typeId'      => 'agent',
        'groupId'     => 'general',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_agent:new-registration.html.twig',
        'newTemplate' => 'SendmailBundle:emails_agent:new_registration.html.twig',
        'viewModel'   => 'AgentNewRegistration',
    ],
    [
        'typeId'      => 'agent',
        'groupId'     => 'general',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_agent:agent-welcome.html.twig',
        'newTemplate' => 'SendmailBundle:emails_agent:agent_welcome.html.twig',
        'viewModel'   => 'AgentWelcome',
    ],

    // Tasks
    [
        'typeId'      => 'agent',
        'groupId'     => 'tasks',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_agent:task-assigned.html.twig',
        'newTemplate' => 'SendmailBundle:emails_agent:task_assigned.html.twig',
        'viewModel'   => 'AgentTaskAssigned',
    ],
    [
        'typeId'      => 'agent',
        'groupId'     => 'tasks',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_agent:task-completed.html.twig',
        'newTemplate' => 'SendmailBundle:emails_agent:task_completed.html.twig',
        'viewModel'   => 'AgentTaskCompleted',
    ],
    [
        'typeId'      => 'agent',
        'groupId'     => 'tasks',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_agent:task-due-reminder.html.twig',
        'newTemplate' => 'SendmailBundle:emails_agent:task_due_reminder.html.twig',
        'viewModel'   => 'AgentTaskDueReminder',
    ],

    //--------------------
    // Alerts and Errors
    //--------------------
    [
        'typeId'      => 'agent',
        'groupId'     => 'alerts',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_agent:login-alert.html.twig',
        'newTemplate' => 'SendmailBundle:emails_agent:login_alert.html.twig',
        'viewModel'   => 'AgentLoginAlert',
    ],
    [
        'typeId'      => 'agent',
        'groupId'     => 'alerts',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_agent:error-invalid-forward.html.twig',
        'newTemplate' => 'SendmailBundle:emails_agent:error_invalid_forward.html.twig',
        'viewModel'   => 'AgentErrorInvalidForward',
    ],
    [
        'typeId'      => 'agent',
        'groupId'     => 'alerts',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_agent:error-marker-missing.html.twig',
        'newTemplate' => 'SendmailBundle:emails_agent:error_marker_missing.html.twig',
        'viewModel'   => 'AgentErrorMarkerMissing',
    ],
    [
        'typeId'      => 'agent',
        'groupId'     => 'alerts',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_agent:error-unknown-from.html.twig',
        'newTemplate' => 'SendmailBundle:emails_agent:error_unknown_from.html.twig',
        'viewModel'   => 'AgentErrorUnknownFrom',
    ],
    [
        'typeId'      => 'agent',
        'groupId'     => 'alerts',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_agent:password-reset-alert.html.twig',
        'newTemplate' => 'SendmailBundle:emails_agent:password_reset_alert.html.twig',
        'viewModel'   => 'AgentPasswordResetAlert',
    ],

    //###################################################################################################################
    // User
    //###################################################################################################################

    //--------------------
    // Tickets
    //--------------------
    [
        'typeId'      => 'user',
        'groupId'     => 'tickets',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
        'newTemplate' => 'SendmailBundle:emails_user:ticket_new_autoreply.html.twig',
        'viewModel'   => 'TicketNewAutoreply',
    ],
    [
        'typeId'      => 'user',
        'groupId'     => 'tickets',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_user:ticket-new-byagent.html.twig',
        'newTemplate' => 'SendmailBundle:emails_user:ticket_new_by_agent.html.twig',
        'viewModel'   => 'TicketNewByAgent',
    ],
    [
        'typeId'      => 'user',
        'groupId'     => 'tickets',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_user:new-ticket-guest.html.twig',
        'newTemplate' => 'SendmailBundle:emails_user:new_ticket_guest.html.twig',
        'viewModel'   => 'NewTicketGuest',
    ],
    [
        'typeId'      => 'user',
        'groupId'     => 'tickets',
        'subGroupId'  => 'validation',
        'name'        => 'DeskPRO:emails_user:new-ticket-validate.html.twig',
        'newTemplate' => 'SendmailBundle:emails_user:new_ticket_validate.html.twig',
        'viewModel'   => 'NewTicketValidate',
    ],
    [
        'typeId'      => 'user',
        'groupId'     => 'tickets',
        'subGroupId'  => 'validation',
        'name'        => 'DeskPRO:emails_user:ticket-new-validate-email.html.twig',
        'newTemplate' => 'SendmailBundle:emails_user:ticket_new_validate_email.html.twig',
        'viewModel'   => 'NewTicketValidateEmail',
    ],
    [
        'typeId'      => 'user',
        'groupId'     => 'tickets',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_user:new-ticket-reg-closed.html.twig',
        'newTemplate' => 'SendmailBundle:emails_user:new_ticket_reg_closed.html.twig',
        'viewModel'   => 'NewTicketRegClosed',
    ],
    [
        'typeId'      => 'user',
        'groupId'     => 'tickets',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_user:ticket-reply-byagent.html.twig',
        'newTemplate' => 'SendmailBundle:emails_user:ticket_reply_by_agent.html.twig',
        'viewModel'   => 'TicketReplyByAgent',
    ],
    [
        'typeId'      => 'user',
        'groupId'     => 'tickets',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_user:ticket-reply-autoreply.html.twig',
        'newTemplate' => 'SendmailBundle:emails_user:ticket_reply_autoreply.html.twig',
        'viewModel'   => 'TicketReplyAutoreply',
    ],
    [
        'typeId'      => 'user',
        'groupId'     => 'tickets',
        'subGroupId'  => 'primary',
        'name'        => 'DeskPRO:emails_user:new-reply-reject-resolved.html.twig',
        'newTemplate' => 'SendmailBundle:emails_user:new_reply_reject_resolved.html.twig',
        'viewModel'   => 'NewReplyRejectResolved',
    ],
    [
        'typeId'      => 'user',
        'groupId'     => 'tickets',
        'subGroupId'  => 'warnings',
        'name'        => 'DeskPRO:emails_user:ticket-awaiting-warn.html.twig',
        'newTemplate' => 'SendmailBundle:emails_user:ticket_awaiting_warn.html.twig',
        'viewModel'   => 'TicketAwaitingWarn',
    ],
    [
        'typeId'      => 'user',
        'groupId'     => 'tickets',
        'subGroupId'  => 'warnings',
        'name'        => 'DeskPRO:emails_user:ticket-awaiting-warn-final.html.twig',
        'newTemplate' => 'SendmailBundle:emails_user:ticket_awaiting_warn_final.html.twig',
        'viewModel'   => 'TicketAwaitingWarnFinal',
    ],
    [
        'typeId'      => 'user',
        'groupId'     => 'tickets',
        'subGroupId'  => 'warnings',
        'name'        => 'DeskPRO:emails_user:ticket-autoclose-warn.html.twig',
        'newTemplate' => 'SendmailBundle:emails_user:ticket_autoclose_warn.html.twig',
        'viewModel'   => 'TicketAutocloseWarn',
    ],
    [
        'typeId'      => 'user',
        'groupId'     => 'tickets',
        'subGroupId'  => 'rating',
        'name'        => 'DeskPRO:emails_user:ticket-rate.html.twig',
        'newTemplate' => 'SendmailBundle:emails_user:ticket_rate.html.twig',
        'viewModel'   => 'TicketRate',
    ],
    [
        'typeId'     => 'user',
        'groupId'    => 'tickets',
        'subGroupId' => 'rating',
        'name'       => 'DeskPRO:emails_common:ticket-rating-links.html.twig',
        'type'       => 'template',
        'viewModel'  => 'TicketRatingLinks',
    ],
    [
        'typeId'      => 'user',
        'groupId'     => 'tickets',
        'subGroupId'  => 'participants',
        'name'        => 'DeskPRO:emails_user:ticket-add-cc.html.twig',
        'newTemplate' => 'SendmailBundle:emails_user:ticket_add_cc.html.twig',
        'type'        => 'template',
        'viewModel'   => 'TicketAddCc',
    ],
    [
        'typeId'      => 'user',
        'groupId'     => 'tickets',
        'subGroupId'  => 'participants',
        'name'        => 'DeskPRO:emails_user:ticket-participant.html.twig',
        'newTemplate' => 'SendmailBundle:emails_user:ticket_participant.html.twig',
        'type'        => 'template',
        'viewModel'   => 'TicketParticipant',
    ],

    //--------------------
    // Account
    //--------------------
    [
        'typeId'     => 'user',
        'groupId'    => 'account',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:register-welcome.html.twig',
    ],
    [
        'typeId'     => 'user',
        'groupId'    => 'account',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:register-welcome-byagent.html.twig',
    ],
    [
        'typeId'     => 'user',
        'groupId'    => 'account',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:email-validation.html.twig',
    ],
    [
        'typeId'     => 'user',
        'groupId'    => 'account',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:new-email-validate-primary.html.twig',
    ],
    [
        'typeId'     => 'user',
        'groupId'    => 'account',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:new-email-validate.html.twig',
    ],
    [
        'typeId'     => 'user',
        'groupId'    => 'account',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:reset-password.html.twig',
    ],
    [
        'typeId'     => 'user',
        'groupId'    => 'account',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:set-password.html.twig',
    ],
    [
        'typeId'     => 'user',
        'groupId'    => 'account',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:agent-changed-password.html.twig',
    ],
    [
        'typeId'     => 'user',
        'groupId'    => 'account',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:account-disabled.html.twig',
    ],

    //--------------------
    // Chat
    //--------------------
    [
        'typeId'     => 'user',
        'groupId'    => 'chat',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:chat-transcript.html.twig',
    ],

    //--------------------
    // Publish
    //--------------------
    [
        'typeId'     => 'user',
        'groupId'    => 'publish',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:comment-approved.html.twig',
    ],
    [
        'typeId'     => 'user',
        'groupId'    => 'publish',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:comment-deleted.html.twig',
    ],
    [
        'typeId'     => 'user',
        'groupId'    => 'publish',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:comment-new.html.twig',
    ],
    [
        'typeId'     => 'user',
        'groupId'    => 'publish',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:download-subscription.html.twig',
    ],
    [
        'typeId'     => 'user',
        'groupId'    => 'publish',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:feedback-subscription.html.twig',
    ],
    [
        'typeId'     => 'user',
        'groupId'    => 'publish',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:kb-subscription.html.twig',
    ],
    [
        'typeId'     => 'user',
        'groupId'    => 'publish',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:news-subscription.html.twig',
    ],

    //--------------------
    // Feedback
    //--------------------
    [
        'typeId'     => 'user',
        'groupId'    => 'feedback',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:feedback-approved.html.twig',
    ],
    [
        'typeId'     => 'user',
        'groupId'    => 'feedback',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:feedback-disapproved.html.twig',
    ],
    [
        'typeId'     => 'user',
        'groupId'    => 'feedback',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:feedback-new.html.twig',
    ],
    [
        'typeId'     => 'user',
        'groupId'    => 'feedback',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:feedback-new-comment.html.twig',
    ],
    [
        'typeId'     => 'user',
        'groupId'    => 'feedback',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:feedback-updated.html.twig',
    ],

    //--------------------
    // Alerts
    //--------------------
    [
        'typeId'     => 'user',
        'groupId'    => 'alerts',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:login-alert.html.twig',
    ],
    [
        'typeId'     => 'user',
        'groupId'    => 'alerts',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:gateway-autoresponse-warn.html.twig',
    ],
    [
        'typeId'     => 'user',
        'groupId'    => 'alerts',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:email-too-big.html.twig',
    ],
    [
        'typeId'     => 'user',
        'groupId'    => 'alerts',
        'subGroupId' => 'primary',
        'name'       => 'DeskPRO:emails_user:rate-limit-notice.html.twig',
    ],
];
