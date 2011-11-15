<?php

namespace Application\ReportBundle\Chart\AmChart;

use Application\ReportBundle\Chart\Base\LineChart as BaseLineChart;

abstract class AmLineChart extends BaseLineChart
{
	public function __construct($stat)
	{
		parent::__construct($stat);

		$this->view_chart_vendor 	= 'AmChart';
		$this->view_chart_class 	= 'Line';
	}

	public function getData()
	{
		$data = $this->stat->getData(new \DateTime(), 10);
		var_dump($data);
		
		$number_points = 7;

		$points = array();
		$points[0] = range(0, $number_points);
		shuffle($points[0]);
		$points[1] = range(0, $number_points);
		shuffle($points[1]);

		$startUnix = time() - (86400 * $number_points);
		for ($unix = $startUnix; $unix < time(); $unix+=86400) {
			$this->series[] = date("d", $unix);
		}
		$this->graphs = array(
			$points[0],
			$points[1]
		);
	}

	public function getFormattedData()
	{
		$this->getData();

		$formattedData = array();
		$formattedData['series'] = $this->series;
		$formattedData['graphs'] = $this->graphs;

		return $formattedData;
	}

	public function getSettings()
	{
		$settings = array();

		return $settings;
	}
}