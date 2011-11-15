<?php

namespace Application\ReportBundle\Chart;

class ChartFactory
{
	const LIMIT = 4;

	/**
	 * Construct a chart based on its class and some data
	 *
	 * @param string $chart_class The class of the chart to construct
	 * @param array $data The raw data to give to the chart
	 */
	public static function getChart($chart_class, $data)
	{
		$count = 0;

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

				$series_set = false;
				foreach ($data as $data_set) {
					$chart->addGraph($data_set['label'], $data_set['values']);

					if (false === $series_set) {
						// Set the series
						foreach ($data_set['values'] as $time=>$value) {
							$chart->addSeries($time);
						}
						$series_set = true;
					}

					$count++;
					if ($count === self::LIMIT) {
						break;
					}
				}

				break;

			/**
			 * AmChart - Pie Chart
			 */
			case 'Application\ReportBundle\Chart\AmChart\PieChart':
				$chart = new $chart_class;

				$series_set = false;
				foreach ($data as $data_set) {
					$value_sum = array_sum($data_set['values']);
					$chart->addSlice($data_set['label'], $value_sum);

					$count++;
					if ($count === self::LIMIT) {
						break;
					}
				}

				break;

			default:
				throw new \Exception("Unsupported chart class: $chart_class");
		}

		return $chart;
	}
}