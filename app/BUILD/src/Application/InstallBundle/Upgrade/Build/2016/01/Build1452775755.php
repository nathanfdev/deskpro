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

namespace Application\InstallBundle\Upgrade\Build;

class Build1452775755 extends AbstractBuild
{
    public function run()
    {
        $this->out('Fix Satisfaction escalation');

        $terms = [
            ['type' => 'date_last_agent_reply', 'op' => 'gte', 'options' => ['date2' => 1, 'value' => 'date']],
            ['type' => 'feedback_rating', 'op' => 'not', 'options' => ['rating' => 'set']],
        ];

        $db = $this->container->getDb();
        $db->update('ticket_escalations', [
            'terms' => json_encode($terms),
            // need to update the date so this fix doesnt cause thousands of emails to get sent suddenly
            'date_created' => date('Y-m-d H:i:s'),
        ], ['sys_name' => 'satisfaction']);
    }
}
