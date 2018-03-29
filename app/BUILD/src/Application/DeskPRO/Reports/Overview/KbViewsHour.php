<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Reports\Overview;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\PageViewLog;
use Orb\Util\Dates;

class KbViewsHour extends AbstractTableOverviewStat
{
    /**
     * @var \DateTime
     */
    protected $date_start;

    /**
     * @var \DateTime
     */
    protected $date_end;

    /**
     * @var int[]
     */
    protected $values = null;

    /**
     * @var array
     */
    protected $titles = null;

    public function __construct(\DateTime $date_start, \DateTime $date_end)
    {
        $this->date_start = $date_start;
        $this->date_end   = $date_end;
    }

    /**
     * @return string[]
     */
    public function getTitles()
    {
        $titles      = array_combine(range(1, 23), range(1, 23));
        $titles['0'] = '0';

        return $titles;
    }

    /**
     * @return int[]
     */
    public function getValues()
    {
        if ($this->values !== null) {
            return $this->values;
        }

        // Convert input datetime which has timezone data, into UTC for db range
        $date1 = Dates::convertToUtcDateTime($this->date_start);
        $date2 = Dates::convertToUtcDateTime($this->date_end);

        $d1 = $date1->format('Y-m-d H:i:s');
        $d2 = $date2->format('Y-m-d H:i:s');

        // Get offset of original date from UTC, we need for mysql
        $offset = $date1->getTimestamp() - $this->date_start->getTimestamp();

        $params = [];
        if ($this->agentTeam) {
            $params['team_id'] = $this->agentTeam;
        }

        $type = PageViewLog::TYPE_ARTICLE;
        $sql  = "
            SELECT HOUR(DATE_SUB(page_view_log.date_created, INTERVAL $offset SECOND)) AS hour, COUNT(*)
            FROM page_view_log
            WHERE page_view_log.object_type = $type AND page_view_log.date_created BETWEEN '$d1' AND '$d2'
            ".($this->agentTeam ? ' AND agent_team_id = :team_id ' : '').'
            GROUP BY hour
        ';

        $this->logger->logDebug("[KbViewsHour] $sql");
        $this->logger->startTimer('KbViewsHour');
        $this->values = App::getDb()->fetchAllKeyValue($sql, $params);
        $this->logger->logTotalTime('KbViewsHour');

        return $this->values;
    }
}
