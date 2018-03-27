<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Reports\Overview;

use Orb\Log\Loggable;
use Orb\Log\Logger;

abstract class AbstractTableOverviewStat implements Loggable
{
    /**
     * @var \Orb\Log\Logger
     */
    protected $logger;

    /**
     * @var int
     */
    protected $agentTeam;

    /**
     * Gets a id => array(info) array of titles. Titles can have children.
     *
     * @abstract
     *
     * @return mixed
     */
    abstract public function getTitles();

    /**
     * Gets an id => xxx of counts.
     *
     * @abstract
     *
     * @return mixed
     */
    abstract public function getValues();

    /**
     * @param \Orb\Log\Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param int $agentTeam
     */
    public function setAgentTeam($agentTeam)
    {
        $this->agentTeam = $agentTeam;
    }

    /**
     * @return \Orb\Log\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return int
     */
    public function getMax()
    {
        if (!$this->getValues()) {
            return 1;
        }

        $max = max($this->getValues());

        if ($max < 3) {
            $max = 3;
        }

        return $max;
    }
}
