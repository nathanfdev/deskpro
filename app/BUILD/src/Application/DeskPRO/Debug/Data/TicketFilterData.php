<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Debug\Data;

use Application\DeskPRO\App;

class TicketFilterData implements DataInterface
{
    public function getData()
    {
        $filter_data = App::getDb()->fetchAll('SELECT * FROM ticket_filters ORDER BY id ASC');
        foreach ($filter_data as &$d) {
            if ($d['terms']) {
                $d['terms'] = @json_decode($d['terms'], true);
            }
        }

        $subs_data = App::getDb()->fetchAll('SELECT * FROM ticket_filter_subscriptions ORDER BY person_id ASC');

        $data                = [];
        $data['filters']     = $filter_data;
        $data['filter_subs'] = $subs_data;

        return $data;
    }
}
