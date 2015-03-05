<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

class Build1395063357 extends AbstractBuild
{
    public function run()
    {
        $filters = $this->container->getDb()->fetchAllKeyValue("
            SELECT id, terms
            FROM ticket_filters
            WHERE sys_name IN ('unassigned', 'unassigned_w_hold')
        ");

        foreach ($filters as $fid => $terms) {
            $terms = unserialize($terms);
            $do_add = true;
            foreach ($terms as $t) {
                if ($t['type'] == 'agent_team') {
                    $do_add = false;
                    break;
                }
            }

            if ($do_add) {
                $terms[] = array('type' => 'agent_team', 'op' => 'is', 'options' => array('agent_team' => '0'));

                $terms = serialize($terms);
                $this->container->getDb()->update('ticket_filters', array('terms' => $terms), array('id' => $fid));
            }
        }
    }
}
