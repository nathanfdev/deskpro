<?php

namespace Application\ReportBundle\Chart\Base;

class LineChart extends AbstractChart
{
	protected $series = array();

	protected $graphs = array();

	const CHART_IDENTIFIER = 'line';
}