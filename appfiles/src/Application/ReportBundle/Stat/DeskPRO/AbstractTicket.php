<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\AbstractStat;
use Application\ReportBundle\Stat\Base\QueryBuilder;

/**
 * Represent Abstract Stats for Tickets
 */
abstract class AbstractTicket extends AbstractStat
{
	public function __construct()
	{
		parent::__construct();
	}
}