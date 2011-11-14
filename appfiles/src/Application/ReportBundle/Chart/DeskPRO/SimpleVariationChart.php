<?php

namespace Application\ReportBundle\Chart\DeskPRO;

use Application\ReportBundle\Chart\Base\AbstractChart as BaseAbstractChart;

/**
 * Display a simple variation change chart
 */
class SimpleVariationChart extends BaseAbstractChart
{
	const CHART_IDENTIFIER = 'simpleVariation';
	
	public function __construct($stat)
	{
		parent::__construct($stat);
		
		$this->view_chart_vendor 	= 'DeskPRO';
		$this->view_chart_class 	= 'SimpleVariation';
		
	}
}
