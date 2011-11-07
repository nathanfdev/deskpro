<?php

namespace Application\ReportBundle\Chart;

abstract class AbstractChart implements ChartInterface
{
	/**
	 * The Stat entity this chart represents
	 *
	 * @var Application\DeskPRO\Entity\Stat
	 */
	protected $stat;
	
	/**
	 *
	 * @param Application\DeskPRO\Entity\Stat $stat The Stat this chart represents
	 */
	public function __construct(Application\DeskPRO\Entity\Stat $stat)
	{
		$this->stat = $stat;
	}
	
	/**
	 * Get the chart Stat
	 *
	 * @return Application\DeskPRO\EntityStat The associated Stat
	 */
	public function getStat()
	{
		return $this->stat;
	}
}