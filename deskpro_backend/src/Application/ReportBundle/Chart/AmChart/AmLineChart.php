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
}