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

namespace Application\DeskPRO\Reports\Overview;

use Application\DeskPRO\App;

class TicketsStatus extends AbstractTableOverviewStat
{
    /**
     * @var int[]
     */
    protected $values = null;


    /**
     * @return string[]
     */
    public function getTitles()
    {
        $s = array(
            'awaiting_agent' => 'Awaiting Agent',
            'awaiting_user'  => 'Awaiting User',
            'resolved'       => 'Resolved',
            'archived'       => 'Archived',
            'hidden'         => 'Hidden'
        );

        $return = array();
        foreach ($s as $k => $v) {
            $return[$k] = $v;
            $return[$k . '_hold'] = $v . ' (On Hold)';
        }

        return $return;
    }


    /**
     * @return int[]
     */
    public function getValues()
    {
        if ($this->values !== null) {
            return $this->values;
        }

        $sql = "
            SELECT tickets.status, COUNT(*)
            FROM tickets AS tickets WHERE is_hold = 0
            GROUP BY tickets.status
        ";

        $this->logger->logDebug("[TicketsStatus] $sql");
        $this->logger->startTimer('TicketsStatus');
        $this->values = App::getDb()->fetchAllKeyValue($sql);
        $this->logger->logTotalTime('TicketsStatus');

        $sql = "
            SELECT CONCAT(tickets.status, '_hold'), COUNT(*)
            FROM tickets AS tickets WHERE is_hold = 1
            GROUP BY tickets.status
        ";

        $this->logger->logDebug("[TicketsStatus w hold] $sql");
        $this->logger->startTimer('TicketsStatus_w_hold');
        $this->values = array_merge($this->values, App::getDb()->fetchAllKeyValue($sql));
        $this->logger->logTotalTime('TicketsStatus_w_hold');

        return $this->values;
    }
}
