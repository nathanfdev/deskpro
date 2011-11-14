<?php

namespace Application\ReportBundle\Chart\Base;

abstract class LineChart extends AbstractChart
{
	protected $series = array();

	protected $graphs = array();

	const CHART_IDENTIFIER = 'line';
}