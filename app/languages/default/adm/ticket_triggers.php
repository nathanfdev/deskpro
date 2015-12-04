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
    'adm.ticket_triggers.triggers'                    => 'Triggers',
    'adm.ticket_triggers.new_trigger'                 => 'New Trigger',
    'adm.ticket_triggers.delete_confirm'              => 'Are you sure you want to delete this trigger? Any actions this trigger used to perform will no longer work.',
    'adm.ticket_triggers.title_explain'               => 'This title will be used throughout the admin interface to refer to this trigger.',
    'adm.ticket_triggers.trigger_newticket_email'     => 'Email the user an automated message about their new ticket',
    'adm.ticket_triggers.no_triggers'                 => 'You have not created any triggers here yet.',
    'adm.ticket_triggers.count_triggers'              => '{{count}} Trigger|{{count}} Triggers',
    'adm.ticket_triggers.count_dep_triggers'          => '{{count}} Department Trigger|{{count}} Department Triggers',
    'adm.ticket_triggers.count_email_triggers'        => '{{count}} Email Account Trigger|{{count}} Email Account Triggers',
    'adm.ticket_triggers.count_satisfaction_triggers' => '{{count}} Satisfaction Trigger|{{count}} Satisfaction Triggers',

    'adm.ticket_triggers.help_department_newticket'  => 'Use these triggers if you want to perform actions based on the department a ticket is created in. Does not apply to tickets created by email.',
    'adm.ticket_triggers.help_department_update'     => 'Use these triggers if you want to perform actions based on when the department is changed to a new department.',
    'adm.ticket_triggers.help_emailacc'              => 'Tickets are created when an email is processed by DeskPRO. These triggers perform actions based on the email address used.',
    'adm.ticket_triggers.help_satisfaction'          => 'Satisfaction triggers.',
    'adm.ticket_triggers.help_satisfaction_disabled' => 'Enable the Satisfaction survey to use these triggers, which perform action based on survey responses.',

    'adm.ticket_triggers.dep_trigger_title'                 => 'Department Trigger',
    'adm.ticket_triggers.dep_trigger_description_newticket' => 'This is a department trigger. It will apply when a new ticket is created with this department.',
    'adm.ticket_triggers.dep_trigger_description_update'    => 'This is a department trigger. It will apply when a ticket is moved into this department.',
    'adm.ticket_triggers.emailacc_trigger_title'            => 'Email Account Trigger',
    'adm.ticket_triggers.emailacc_trigger_description'      => 'This is a email account trigger. It will apply when a new ticket is created by emailing this email address.',
    'adm.ticket_triggers.satisfaction_trigger_title'        => 'Satisfaction Trigger',
    'adm.ticket_triggers.satisfaction_trigger_description'  => 'Satisfaction Trigger.',

);
