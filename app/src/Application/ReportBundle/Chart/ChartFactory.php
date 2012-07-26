<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
* DeskPRO
*
* @package DeskPRO
*/

namespace Application\ReportBundle\Chart;

use Application\DeskPRO\Entity\Stat;

class ChartFactory
{
	/**
	 * Construct a chart based on its class and some data
	 *
	 * TODO: Should pass in series here, rather than trying to work it out
	 *
	 * @param string $chart_class The class of the chart to construct
	 * @param array $data The raw data to give to the chart
	 * @param Stat The Stat entity
	 */
	public static function getChart($chart_class, $data, Stat $stat)
	{
		$display_unit = '';
		if (false === is_null($stat->getFormatter())) {
			// If there is a formatter, it may want to normalize the data,
			$normalized_result = $stat->getFormatter()->normalizeData($data);
			$data 		= $normalized_result['data'];
			$display_unit 	= $normalized_result['unit'];
		}

		$concept_class = $stat->stat_concept_class;
		$label_name = $concept_class::getLabelName();

		switch ($chart_class) {
			/**
			 * AmChart - Line Chart
			 * AmChart - Stacked Line Chart
			 * AmChart - Column Chart
			 * AmChart - Stacked Column Chart
			 */
			case 'Application\ReportBundle\Chart\AmChart\LineChart':
			case 'Application\ReportBundle\Chart\AmChart\StackedLineChart':
			case 'Application\ReportBundle\Chart\AmChart\ColumnChart':
			case 'Application\ReportBundle\Chart\AmChart\StackedColumnChart':

				/** @var $chart \Application\ReportBundle\Chart\Base\SeriesChart */
				$chart = new $chart_class;

				$chart->setFormatter($stat->getFormatter());
				$chart->setDisplayUnits($display_unit);

				$chart->setYLabel($label_name);

				$series_set = false;
				foreach ($data as $data_set) {
					$chart->addGraph($data_set['label'], $data_set['values']);

					if (false === $series_set) {
						// Set the series
						foreach ($data_set['values'] as $time=>$value) {
							switch ($stat->getRunFrequency()) {
								case 'hourly':
									$formatted_series = date("H", strtotime($time));
									$chart->setXLabel('Hour');
									break;
								case 'daily':
									$formatted_series = date("j", strtotime($time));
									$chart->setXLabel('Day');
									break;
								case 'monthly':
									$formatted_series = date("M", strtotime($time));
									$chart->setXLabel('Month');
									break;
								case 'yearly':
									$formatted_series = date("Y", strtotime($time));
									$chart->setXLabel('Year');
									break;
							}
							$chart->addSeries($formatted_series);
						}
						$series_set = true;
					}
				}

				break;

			/**
			 * AmChart - Pie Chart
			 */
			case 'Application\ReportBundle\Chart\AmChart\PieChart':
				$chart = new $chart_class;

				$chart->setFormatter($stat->getFormatter());
				$chart->setDisplayUnits($display_unit);

				$series_set = false;
				foreach ($data as $data_set) {
					if ('time_formatter' === $chart->getFormatterIdentifier()) {
						// Time data needs to be divided by the number of points
						$value_sum = (count($data_set['values']) != 0) ? array_sum($data_set['values']) / count($data_set['values']) : 0;
					}
					else {
						$value_sum = array_sum($data_set['values']);
					}

					$chart->addSlice($data_set['label'], $value_sum);
				}

				$chart->sortData('label');

				break;

			/**
			 * DeskPRO - Simple Variaiton Chart
			 */
			case 'Application\ReportBundle\Chart\DeskPRO\SimpleVariationChart':
				$chart = new $chart_class;

				$chart->setFormatter($stat->getFormatter());
				$chart->setDisplayUnits($display_unit);

				$chart->setDifferenceDirection($stat->getVariation());

				// We can only compare one set of data, if there
				// are others they are simply discarded
				if (count($data)) {
					$data_set = array_shift($data);
					$chart->addDataPoints($data_set['values']);
				}

				break;

			/**
			 * DeskPRO - Simple Drilldown Chart
			 */
			case 'Application\ReportBundle\Chart\DeskPRO\SimpleDrillDownChart':
				$chart = new $chart_class;

				$chart->setFormatter($stat->getFormatter());
				$chart->setDataLabel($stat->getGroupingName());
				$chart->setDisplayUnits($display_unit);

				foreach ($data as $data_set) {
					if ('time_formatter' === $chart->getFormatterIdentifier()) {
						// Time data needs to be divided by the number of points
						$value_sum = (count($data_set['values']) != 0) ? array_sum($data_set['values']) / count($data_set['values']) : 0;
					}
					else {
						$value_sum = array_sum($data_set['values']);
					}

					$chart->addRow($data_set['label'], $data_set['values'], $value_sum);
				}

				$chart->sortData('label');

				break;

			/**
			 * DeskPRO - Detailed Drilldown Chart
			 */
			case 'Application\ReportBundle\Chart\DeskPRO\DetailedDrillDownChart':
				$chart = new $chart_class;

				$chart->setFormatter($stat->getFormatter());
				$chart->setDataLabel($stat->getGroupingName());
				$chart->setDisplayUnits($display_unit);

				$chart->setDifferenceDirection($stat->getVariation());

				foreach ($data as $data_set) {
					if ('time_formatter' === $chart->getFormatterIdentifier()) {
						// Time data needs to be divided by the number of points
						$value_sum = (count($data_set['values']) != 0) ? array_sum($data_set['values']) / count($data_set['values']) : 0;
					}
					else {
						$value_sum = array_sum($data_set['values']);
					}

					$chart->addRow($data_set['label'], $data_set['values'], $value_sum);
				}

				$chart->sortData('label');

				break;

			default:
				throw new \Exception("Unsupported chart class: $chart_class");
		}

		return $chart;
	}

	/**
	 * Transform a chart class to its full screen view class
	 */
	public static function transformChartToFullScreen($chart_class)
	{
		switch ($chart_class) {
			case 'Application\ReportBundle\Chart\DeskPRO\SimpleVariationChart':
				$chart_class = 'Application\ReportBundle\Chart\AmChart\LineChart';
				break;
		}

		return $chart_class;
	}
}