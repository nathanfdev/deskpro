<?php

namespace Application\ReportBundle\Chart\AmChart;

use Application\ReportBundle\Chart\Base\ColumnChart as BaseColumnChart;

class ColumnChart extends BaseColumnChart
{	
	public function __construct($stat)
	{
		parent::__construct($stat);
		
		$this->view_chart_vendor 	= 'AmChart';
		$this->view_chart_class 	= 'Column';
		
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