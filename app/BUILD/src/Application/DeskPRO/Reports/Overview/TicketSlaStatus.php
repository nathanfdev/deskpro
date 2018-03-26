<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Reports\Overview;

use Application\DeskPRO\App;
use Orb\Util\Dates;

class TicketSlaStatus extends AbstractTableOverviewStat
{
    /**
     * @var int[]
     */
    protected $values = null;

    /**
     * @var int
     */
    protected $sla_id;

    /**
     * @var \DateTime
     */
    protected $date_start;

    /**
     * @var \DateTime
     */
    protected $date_end;

    /**
     * @param int       $sla_id
     * @param \DateTime $date_start
     * @param \DateTime $date_end
     */
    public function __construct($sla_id = null, \DateTime $date_start = null, \DateTime $date_end = null)
    {
        $this->sla_id     = $sla_id ? (int) $sla_id : null;
        $this->date_start = $date_start;
        $this->date_end   = $date_end;
    }

    /**
     * @return string[]
     */
    public function getTitles()
    {
        return [
            'ok'      => 'Passed',
            'warning' => 'Warning',
            'fail'    => 'Failed',
        ];
    }

    /**
     * @return int[]
     */
    public function getValues()
    {
        if ($this->values !== null) {
            return $this->values;
        }

        $where = [];

        if ($this->sla_id) {
            $where[] = "ticket_slas.sla_id = {$this->sla_id}";
        }
        if ($this->date_start && $this->date_end) {
            $date1 = Dates::convertToUtcDateTime($this->date_start);
            $date2 = Dates::convertToUtcDateTime($this->date_end);

            $d1 = $date1->format('Y-m-d H:i:s');
            $d2 = $date2->format('Y-m-d H:i:s');

            $where[] = "tickets.date_created BETWEEN '$d1' AND '$d2'";
        }

        $params = [];
        if ($this->agentTeam) {
            $params['team_id'] = $this->agentTeam;
            $where[]           = 'agent_team_id = :team_id';
        }

        if ($where) {
            $where = ' WHERE '.implode(' AND ', $where);
        } else {
            $where = '';
        }

        $sql = "
            SELECT ticket_slas.sla_status, COUNT(*) AS count
            FROM ticket_slas
            INNER JOIN tickets ON (ticket_slas.ticket_id = tickets.id)
            INNER JOIN slas ON (ticket_slas.sla_id = slas.id)
            $where
            GROUP BY ticket_slas.sla_status
        ";

        $this->logger->logDebug("[TicketSlaStatus] $sql");
        $this->logger->startTimer('TicketSlaStatus');
        $this->values = App::getDb()->fetchAllKeyValue($sql, $params);
        $this->logger->logTotalTime('TicketSlaStatus');

        return $this->values;
    }
}
