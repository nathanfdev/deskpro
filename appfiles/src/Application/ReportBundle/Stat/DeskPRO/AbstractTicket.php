<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\AbstractStat;
use Application\ReportBundle\Stat\Base\QueryBuilder;
use Application\ReportBundle\Stat\Searcher\TicketSearch;

/**
 * Represent Abstract Stats for Tickets
 */
abstract class AbstractTicket extends AbstractStat
{
        public function init()
        {
                // Set the searcher to use, we want the ticket searcher
		$this->setSearcher(new TicketSearch());
        }
}