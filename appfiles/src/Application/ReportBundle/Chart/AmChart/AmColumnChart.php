<?php

namespace Application\ReportBundle\Chart\AmChart;

use Application\ReportBundle\Chart\Base\ColumnChart as BaseColumnChart;

abstract class AmColumnChart extends BaseColumnChart
{
	public function __construct()
	{
		$this->view_chart_vendor 	= 'AmChart';
		$this->view_chart_class 	= 'Column';
	}
}