<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Debug\Data;

use Application\DeskPRO\App;

class TicketTriggerData implements DataInterface
{
    public function getData()
    {
        $ret = [];

        $data = App::getDb()->fetchAll('SELECT * FROM ticket_triggers ORDER BY id ASC');
        foreach ($data as &$d) {
            if (!empty($d['terms'])) {
                $d['terms'] = @json_decode($d['terms'], true);
            }
            if (!empty($d['actions'])) {
                $d['actions'] = @json_decode($d['actions'], true);
            }
        }
        unset($d);
        $ret['triggers'] = $data;

        $data        = App::getDb()->fetchAll('SELECT * FROM slas ORDER BY id ASC');
        $ret['slas'] = $data;

        $data = App::getDb()->fetchAll('SELECT * FROM ticket_escalations ORDER BY id ASC');
        foreach ($data as &$d) {
            if (!empty($d['terms'])) {
                $d['terms'] = @json_decode($d['terms'], true);
            }
            if (!empty($d['terms_any'])) {
                $d['terms_any'] = @json_decode($d['terms_any'], true);
            }
            if (!empty($d['actions'])) {
                $d['actions'] = @json_decode($d['actions'], true);
            }
        }
        unset($d);
        $ret['escalations'] = $data;

        return $ret;
    }
}
