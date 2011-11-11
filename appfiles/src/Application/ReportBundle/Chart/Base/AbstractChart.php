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

	/**
	 * Get the view chart vendor
	 *
	 * @return string The view chart vendor
	 */
	public function getViewChartVendor()
	{
		return $this->view_chart_vendor;
	}

	/**
	 * Get the view chart class
	 *
	 * @return string The view chart class
	 */
	public function getViewChartClass()
	{
		return $this->view_chart_class;
	}

	/**
	 * Get the labels for the chart
	 *
	 * @return array List of labels
	 */
	public function getLabels()
	{
		$labels = array();

		foreach ($this->stat->getReferenceLookup() as $lookup) {
			$labels[] = $lookup;
		}
		
		return $labels;
	}
}