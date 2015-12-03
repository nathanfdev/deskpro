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

namespace Application\InstallBundle\Upgrade\Build;

class Build1448627929 extends AbstractBuild
{
    public function run()
    {
        $this->out('Upgrade validation settings');

        $trigger_modes = explode(',', strtolower($this->container->getDb()->fetchColumn("
            SELECT by_user_mode
            FROM ticket_triggers
            WHERE sys_name = 'default_newticket_requirevalid' AND is_enabled = 1
        ") ?: ''));

        $email_validate_email = in_array('email', $trigger_modes);
        $email_validate_web   = in_array('portal', $trigger_modes);

        $this->container->getDb()->deleteIn('settings', ['core_tickets.web_require_validation', 'core_tickets.email_require_validation'], 'name');
        $this->container->getDb()->insert('settings', [
            ['name' => 'core_tickets.web_require_validation', 'value' => (int) $email_validate_web],
            ['name' => 'core_tickets.email_require_validation', 'value' => (int) $email_validate_email],
        ]);

        $this->container->getDb()->deleteIn('ticket_triggers', array(
            'default_newticket_requirevalid', // the one that used to enable/disable it
            'default_newticket_validemail',   // the one that checks for it
            'default_newticket_validagent',    // the one that cheked for agent validation
        ), 'sys_name');
    }
}
