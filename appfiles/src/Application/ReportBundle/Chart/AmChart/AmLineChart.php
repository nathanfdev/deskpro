<?php

namespace Application\ReportBundle\Chart\AmChart;

use Application\ReportBundle\Chart\Base\LineChart as BaseLineChart;

abstract class AmLineChart extends BaseLineChart
{
	public function __construct()
	{
		$this->view_chart_vendor 	= 'AmChart';
		$this->view_chart_class 	= 'Line';
	}

	public function getData()
	{
		$points = array();
		foreach ($data as $point) {
			$this->series[] = date("d", $point['stat_unix']);
			$points[0][] = $point['value'];
		}

		$this->graphs = array(
			$points[0]
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