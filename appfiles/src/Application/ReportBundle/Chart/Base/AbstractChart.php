<?php

namespace Application\ReportBundle\Chart\Base;

abstract class AbstractChart implements ChartInterface
{
	/**
	 * Unique identifier of chart type
	 */
	const CHART_IDENTIFIER = '';
	
	protected $chart_type = '';
	
	protected $chart_vendor = '';
	
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
	public function __construct($stat)
	{
		$this->stat = $stat;
	}
	
	/**
	 * Get the chart Stat
	 *
	 * @return Application\DeskPRO\Entity\Stat The associated Stat
	 */
	public function getStat()
	{
		return $this->stat;
	}
	
	/**
	 * Set the chart Stat
	 *
	 * @param Application\DeskPRO\Entity\Stat $stat The Stat
	 */
	public function setStat(Application\DeskPRO\Entity\Stat $stat)
	{
		$this->stat = $stat;
	}
	
	public function getChartVendor()
	{
		return $this->chart_vendor;
	}
	
	public function getChartClass()
	{
		return $this->chart_type;
	}
}