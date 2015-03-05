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
 */

namespace Application\DeskPRO\Debug\Data;

use Application\DeskPRO\App;

class TicketLayoutsData implements DataInterface
{
    public function getData()
    {
        $filter_data = App::getDb()->fetchAll("SELECT * FROM ticket_layouts ORDER BY id ASC");
        foreach ($filter_data as &$d) {
            if ($d['user_layout']) {
                $d['user_layout'] = @json_decode($d['user_layout'], true);
            }
            if ($d['agent_layout']) {
                $d['agent_layout'] = @json_decode($d['agent_layout'], true);
            }
        }

        $data = array();
        $data['ticket_layouts'] = $filter_data;

        return $data;
    }
}
