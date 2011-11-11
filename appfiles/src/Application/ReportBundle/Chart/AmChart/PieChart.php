<?php

namespace Application\ReportBundle\Chart\AmChart;

use Application\ReportBundle\Chart\Base\PieChart as BasePieChart;

/**
 * Pie Chart representation
 */
class PieChart extends BasePieChart
{
	public function __construct($stat)
	{
		parent::__construct($stat);
		
		$this->view_chart_vendor 	= 'AmChart';
		$this->view_chart_class 	= 'Pie';	
	}
	
	public function getData()
	{
		$number_points = 20;
		
		$points = range(0, $number_points);
		shuffle($points);
		
		$startUnix = time() - (86400 * $number_points);
		for ($unix = $startUnix; $unix < time(); $unix+=86400) {
			$this->series[] = date("d-m-Y", $unix);
		}
		$this->graphs = array(
			$points	
		);
	}
	
	public function getFormattedData()
	{
		$this->getData();
		
		$formattedData = array();	
		
		return $formattedData;
	}
	
	public function getSettings()
	{
		$settings = array();
		
		return $settings;
	}
}