<?php

namespace Application\ReportBundle\Chart\AmChart;

use Application\ReportBundle\Chart\Base\LineChart as BaseLineChart;

class LineChart extends BaseLineChart
{
	public function __construct($stat)
	{
		parent::__construct($stat);
		
		$this->view_chart_vendor 	= 'AmChart';
		$this->view_chart_class 	= 'Line';	
	}
	
	public function getData()
	{
		$points = range(0, 10);
		shuffle($points);
		
		$this->series = range(1, 10);
		$this->graphs = array(
			$points	
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