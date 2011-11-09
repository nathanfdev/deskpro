<?php

namespace Application\ReportBundle\Chart\AmChart;

use Application\ReportBundle\Chart\Base\LineChart as BaseLineChart;

class LineChart extends BaseLineChart
{
	public function __construct($stat)
	{
		parent::__construct($stat);
		
		$this->chart_vendor 	= 'AmChart';
		$this->chart_type 	= 'Line';	
	}
	
	public function getFormattedData()
	{
		$formattedData = array();
		
		return $formattedData;
	}
	
	public function getSettings()
	{
		$settings = array();
		
		return $settings;
	}
}