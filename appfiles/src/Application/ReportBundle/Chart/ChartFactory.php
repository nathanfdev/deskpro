<?php

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
		$data = $stat->getFormatter()->normalizeData($data);

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
				$chart = new $chart_class;

				$chart->setFormatter($stat->getFormatter());

				$series_set = false;
				foreach ($data as $data_set) {
					$chart->addGraph($data_set['label'], $data_set['values']);

					if (false === $series_set) {
						// Set the series
						foreach ($data_set['values'] as $time=>$value) {
							switch ($stat->getRunFrequency()) {
								case 'daily':
									$formatted_series = date("j", strtotime($time));
									break;
								case 'monthly':
									$formatted_series = date("M", strtotime($time));
									break;
								case 'yearly':
									$formatted_series = date("Y", strtotime($time));
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