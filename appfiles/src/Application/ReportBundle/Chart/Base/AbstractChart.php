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
	 * The end date used as a refernce point to retrieve data upto
	 */
	protected $data_end_date = null;

	/**
	 * The number of data points to retrieve for this graph
	 */
	protected $data_point_count = null;

	/**
	 * The RAW chart data
	 */
	protected $data = array();

	/**
	 * List of labels
	 *
	 * @var array
	 */
	protected $labels = array();

	/**
	 * Flag to indicate if we have cached the data yet
	 *
	 * @var bool
	 */
	protected $cached_data = false;

	/**
	 * Flag to indicate if we have cached the labels  yet
	 *
	 * @var bool
	 */
	protected $cached_labels = false;

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
	 * The end date used as a reference point to get the data upto
	 *
	 * @param \DateTime $date_time
	 */
	public function setDataEndDate(\DateTime $date_time)
	{
		$this->data_end_date = $date_time;
	}

	/**
	 * Set the number of data points to get
	 *
	 * @param int $count
	 */
	public function setDataPointCount($count)
	{
		$this->data_point_count = $count;
	}

	/**
	 * Get the number of data points we want
	 *
	 * @return int
	 */
	public function getDataPointCount()
	{
		// If its not set, use the Stat default
		if (true === is_null($this->data_point_count)) {
			return $this->stat->getDefaultDataPointCount();
		}
		else {
			return $this->data_point_count;
		}
	}

	/**
	 * Get the RAW chart data
	 *
	 * @return array Raw chart data
	 */
	public function getData()
	{
		// Have we already cached the data?
		if (true === $this->cached_data) {
			return $this->data;
		}

		$this->data = $this->stat->getData($this->data_end_date, $this->getDataPointCount(), true);

		// Set the cache flag
		$this->cached_data = true;

		return $this->data;
	}

	/**
	 * Get the labels for the chart
	 *
	 * @return array List of labels
	 */
	public function getLabels()
	{
		// Have we already cached the labels?
		if (true === $this->cached_labels) {
			return $this->labels;
		}

		$this->labels = $this->stat->getReferenceLookup($this->data_end_date, $this->getDataPointCount());

		// Set the cache flag
		$this->cached_labels = true;

		return $this->labels;
	}

	/**
	 * Get a label by its reference index
	 *
	 * @param int $index
	 * @return string
	 */
	public function getLabel($index)
	{
		// Have we got the labels yet?
		if (false === $this->cached_labels) {
			$this->getLabel();
		}

		// No check if label exsists
		return (isset($this->labels[$index])) ? $this->labels[$index] : null;
	}
}