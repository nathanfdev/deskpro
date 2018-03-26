<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Debug\Data;

use Application\DeskPRO\App;

class TicketLayoutsData implements DataInterface
{
    public function getData()
    {
        $filter_data = App::getDb()->fetchAll('SELECT * FROM ticket_layouts ORDER BY id ASC');
        foreach ($filter_data as &$d) {
            if ($d['user_layout']) {
                $d['user_layout'] = @json_decode($d['user_layout'], true);
            }
            if ($d['agent_layout']) {
                $d['agent_layout'] = @json_decode($d['agent_layout'], true);
            }
        }

        $data                   = [];
        $data['ticket_layouts'] = $filter_data;

        return $data;
    }
}
