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
    ####################################################################################################################
    # Layout
    ####################################################################################################################
    array(
        'typeId'  => 'layout',
        'groupId' => 'top',
        'name'    => 'DeskPRO:emails_common:email-header.html.twig',
        'type'    => 'template',
    ),
    array(
        'typeId'  => 'layout',
        'groupId' => 'top',
        'name'    => 'DeskPRO:emails_common:email-footer.html.twig',
        'type'    => 'template',
    ),
    array(
        'typeId'  => 'layout',
        'groupId' => 'top',
        'name'    => 'DeskPRO:emails_common:email-custom-css.css.twig',
        'type'    => 'template',
    ),
    array(
        'typeId'  => 'layout',
        'groupId' => 'top',
        'name'    => 'DeskPRO:emails_common:email-css.css.twig',
        'type'    => 'template',
    ),

    ####################################################################################################################
    # Agent
    ####################################################################################################################

    //--------------------
    // Tickets
    //--------------------
    array(
        'typeId'  => 'agent',
        'groupId' => 'tickets',
        'name'    => 'DeskPRO:emails_agent:ticket-new.html.twig',
    ),
    array(
        'typeId'  => 'agent',
        'groupId' => 'tickets',
        'name'    => 'DeskPRO:emails_agent:ticket-update.html.twig',
    ),
    array(
        'typeId'  => 'agent',
        'groupId' => 'tickets',
        'name'    => 'DeskPRO:emails_agent:ticket-reply.html.twig',
    ),
    array(
        'typeId'  => 'agent',
        'groupId' => 'tickets',
        'name'    => 'DeskPRO:emails_common:ticket-props-table.html.twig',
        'type'    => 'template',
    ),
    array(
        'typeId'  => 'agent',
        'groupId' => 'tickets',
        'name'    => 'DeskPRO:emails_common:ticket-fwd-out-header.html.twig',
        'type'    => 'template',
    ),

    //--------------------
    // General
    //--------------------
    array(
        'typeId'  => 'agent',
        'groupId' => 'general',
        'name'    => 'DeskPRO:emails_agent:new-agent-chat-message.html.twig',
    ),
    array(
        'typeId'  => 'agent',
        'groupId' => 'general',
        'name'    => 'DeskPRO:emails_agent:new-comment.html.twig',
    ),
    array(
        'typeId'  => 'agent',
        'groupId' => 'general',
        'name'    => 'DeskPRO:emails_agent:new-feedback.html.twig',
    ),
    array(
        'typeId'  => 'agent',
        'groupId' => 'general',
        'name'    => 'DeskPRO:emails_agent:new-registration.html.twig',
    ),
    array(
        'typeId'  => 'agent',
        'groupId' => 'general',
        'name'    => 'DeskPRO:emails_agent:agent-welcome.html.twig',
    ),

    // Tasks
    array(
        'typeId'  => 'agent',
        'groupId' => 'tasks',
        'name'    => 'DeskPRO:emails_agent:task-assigned.html.twig',
    ),
    array(
        'typeId'  => 'agent',
        'groupId' => 'tasks',
        'name'    => 'DeskPRO:emails_agent:task-completed.html.twig',
    ),
    array(
        'typeId'  => 'agent',
        'groupId' => 'tasks',
        'name'    => 'DeskPRO:emails_agent:task-due-reminder.html.twig',
    ),

    //--------------------
    // Alerts and Errors
    //--------------------
    array(
        'typeId'  => 'agent',
        'groupId' => 'alerts',
        'name'    => 'DeskPRO:emails_agent:login-alert.html.twig',
    ),
    array(
        'typeId'  => 'agent',
        'groupId' => 'alerts',
        'name'    => 'DeskPRO:emails_agent:error-invalid-forward.html.twig',
    ),
    array(
        'typeId'  => 'agent',
        'groupId' => 'alerts',
        'name'    => 'DeskPRO:emails_agent:error-marker-missing.html.twig',
    ),
    array(
        'typeId'  => 'agent',
        'groupId' => 'alerts',
        'name'    => 'DeskPRO:emails_agent:error-unknown-from.html.twig',
    ),
    array(
        'typeId'  => 'agent',
        'groupId' => 'alerts',
        'name'    => 'DeskPRO:emails_agent:password-reset-alert.html.twig',
    ),

    ####################################################################################################################
    # Agent
    ####################################################################################################################

    //--------------------
    // Tickets
    //--------------------
    array(
        'typeId'  => 'user',
        'groupId' => 'tickets',
        'name'    => 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
    ),
    array(
        'typeId'  => 'user',
        'groupId' => 'tickets',
        'name'    => 'DeskPRO:emails_user:ticket-new-byagent.html.twig',
    ),
    array(
        'typeId'  => 'user',
        'groupId' => 'tickets',
        'name'    => 'DeskPRO:emails_user:ticket-new-validate-email.html.twig',
    ),
    array(
        'typeId'  => 'user',
        'groupId' => 'tickets',
        'name'    => 'DeskPRO:emails_user:new-ticket-reg-closed.html.twig',
    ),
    array(
        'typeId'  => 'user',
        'groupId' => 'tickets',
        'name'    => 'DeskPRO:emails_user:ticket-reply-byagent.html.twig',
    ),
    array(
        'typeId'  => 'user',
        'groupId' => 'tickets',
        'name'    => 'DeskPRO:emails_user:ticket-reply-autoreply.html.twig',
    ),
    array(
        'typeId'  => 'user',
        'groupId' => 'tickets',
        'name'    => 'DeskPRO:emails_user:new-reply-reject-resolved.html.twig',
    ),
    array(
        'typeId'  => 'user',
        'groupId' => 'tickets',
        'name'    => 'DeskPRO:emails_user:ticket-autoclose-warn.html.twig',
    ),
    array(
        'typeId'  => 'user',
        'groupId' => 'tickets',
        'name'    => 'DeskPRO:emails_user:ticket-rate.html.twig',
    ),
    array(
        'typeId'  => 'user',
        'groupId' => 'tickets',
        'name'    => 'DeskPRO:emails_common:ticket-rating-links.html.twig',
        'type'    => 'template',
    ),

    //--------------------
    // Account
    //--------------------
    array(
        'typeId'  => 'user',
        'groupId' => 'account',
        'name'    => 'DeskPRO:emails_user:register-welcome.html.twig',
    ),
    array(
        'typeId'  => 'user',
        'groupId' => 'account',
        'name'    => 'DeskPRO:emails_user:register-welcome-byagent.html.twig',
    ),
    array(
        'typeId'  => 'user',
        'groupId' => 'account',
        'name'    => 'DeskPRO:emails_user:register-validate.html.twig',
    ),
    array(
        'typeId'  => 'user',
        'groupId' => 'account',
        'name'    => 'DeskPRO:emails_user:reset-password.html.twig',
    ),
    array(
        'typeId'  => 'user',
        'groupId' => 'account',
        'name'    => 'DeskPRO:emails_user:agent-changed-password.html.twig',
    ),
    array(
        'typeId'  => 'user',
        'groupId' => 'account',
        'name'    => 'DeskPRO:emails_user:account-disabled.html.twig',
    ),

    //--------------------
    // Chat
    //--------------------
    array(
        'typeId'  => 'user',
        'groupId' => 'chat',
        'name'    => 'DeskPRO:emails_user:chat-transcript.html.twig',
    ),

    //--------------------
    // Publish
    //--------------------
    array(
        'typeId'  => 'user',
        'groupId' => 'publish',
        'name'    => 'DeskPRO:emails_user:comment-approved.html.twig',
    ),
    array(
        'typeId'  => 'user',
        'groupId' => 'publish',
        'name'    => 'DeskPRO:emails_user:comment-deleted.html.twig',
    ),
    array(
        'typeId'  => 'user',
        'groupId' => 'publish',
        'name'    => 'DeskPRO:emails_user:comment-new.html.twig',
    ),
    array(
        'typeId'  => 'user',
        'groupId' => 'publish',
        'name'    => 'DeskPRO:emails_user:kb-subscription.html.twig',
    ),

    //--------------------
    // Feedback
    //--------------------
    array(
        'typeId'  => 'user',
        'groupId' => 'feedback',
        'name'    => 'DeskPRO:emails_user:feedback-approved.html.twig',
    ),
    array(
        'typeId'  => 'user',
        'groupId' => 'feedback',
        'name'    => 'DeskPRO:emails_user:feedback-disapproved.html.twig',
    ),
    array(
        'typeId'  => 'user',
        'groupId' => 'feedback',
        'name'    => 'DeskPRO:emails_user:feedback-new.html.twig',
    ),
    array(
        'typeId'  => 'user',
        'groupId' => 'feedback',
        'name'    => 'DeskPRO:emails_user:feedback-new-comment.html.twig',
    ),
    array(
        'typeId'  => 'user',
        'groupId' => 'feedback',
        'name'    => 'DeskPRO:emails_user:feedback-updated.html.twig',
    ),

    //--------------------
    // Alerts
    //--------------------
    array(
        'typeId'  => 'user',
        'groupId' => 'alerts',
        'name'    => 'DeskPRO:emails_user:gateway-autoresponse-warn.html.twig',
    ),
    array(
        'typeId'  => 'user',
        'groupId' => 'alerts',
        'name'    => 'DeskPRO:emails_user:email-too-big.html.twig',
    ),
);
