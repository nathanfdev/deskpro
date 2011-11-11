<?php

namespace Application\ReportBundle\Chart\Base;

class LineChart extends AbstractChart
{
	protected $series = array();

	protected $graphs = array();

	const CHART_IDENTIFIER = 'line';

	public function getLabels()
	{
		$labels = array();

		foreach ($this->stat->getReferenceLookup() as $lookup) {
			$labels[] = $lookup;
		}
		
		return $labels;
	}

}