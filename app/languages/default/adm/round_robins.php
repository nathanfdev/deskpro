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
    'adm.round_robins.enable'        => 'Enable Round Robin',
    'adm.round_robins.count'         => '{{count}} Round Robin|{{count}} Round Robins',
    'adm.round_robins.disable_title' => 'Disabling Round Robin',
    'adm.round_robins.disable_warn'  => 'Disabling Round Robin will disable {{count}} trigger and remove the round robin actions. You should update or delete affected trigger|Disabling Round Robin will disable {{count}} triggers and remove the round robin actions. You should update or delete affected triggers',

    'adm.round_robins.delete_warn' => 'Are you sure you want to delete this Round Robin? This operation could not be undone. Deleting this Round Robin will disable {{count}} trigger and remove the round robin actions.|Are you sure you want to delete this Round Robin? This operation could not be undone. Deleting this Round Robin will disable {{count}} triggers and remove the round robin actions.',

    'adm.round_robins.title'              => 'Title',
    'adm.round_robins.online_title'       => 'Online Only?',
    'adm.round_robins.online_description' => 'Only Assign to Agents that are Online',
    'adm.round_robins.logs_title'         => 'Logs',
    'adm.round_robins.logs_view'          => 'View logs',
    'adm.round_robins.agents'             => 'Agents',
    'adm.round_robins.next'               => 'Next in queue',
    'adm.round_robins.help_bulk'          => 'Bulk add agents that are members of teams, departments or permission groups',

    'adm.round_robins.log_assigned'         => 'Assigned to {{ name }}',
    'adm.round_robins.log_skipped_disabled' => 'Skipped disabled {{ name }}',
    'adm.round_robins.log_skipped_offline'  => 'Skipped offline {{ name }}',
    'adm.round_robins.log_no_agents_online' => 'No Agents online: Remains Unassigned',
);
