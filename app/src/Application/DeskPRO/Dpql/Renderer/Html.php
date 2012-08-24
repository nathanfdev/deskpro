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
 * @subpackage Dpql
 */

namespace Application\DeskPRO\Dpql\Renderer;

use Application\DeskPRO\Dpql\ResultHandler;
use Application\DeskPRO\Dpql\Results;
use Application\DeskPRO\App;

/**
 * Renders DPQL results to HTML.
 */
class Html extends AbstractRenderer
{
	/**
	 * Internal handler used when rendering to count how many rows
	 * row spans need to be used for.
	 *
	 * @var array
	 */
	protected $_rowSpans = array();

	/**
	 * Internal handler used when rendering to determine which row groups
	 * have been "hit" and printed.
	 *
	 * @var array
	 */
	protected $_rowGroupHit = array();

	/**
	 * Get the default value renderer that should be used for this type.
	 *
	 * @return \Application\DeskPRO\Dpql\Renderer\Values\AbstractValues
	 */
	protected function _getDefaultValueRenderer()
	{
		return new \Application\DeskPRO\Dpql\Renderer\Values\Html();
	}

	/**
	 * Gets the MIME content type for this type of output.
	 *
	 * @return string
	 */
	public function getContentType()
	{
		return 'text/html';
	}

	/**
	 * Gets the file extension for this type of output.
	 *
	 * @return string
	 */
	public function getExtension()
	{
		return 'html';
	}

	/**
	 * Joins the already rendered output into one output.
	 *
	 * @param array $output
	 *
	 * @return string
	 */
	protected function _implodeSplitOutput(array $output)
	{
		return implode("\n\n", $output);
	}

	/**
	 * Finalizes the rendering of a split output by rendering the body with the header.
	 *
	 * @param string $header
	 * @param string $body
	 *
	 * @return string
	 */
	public function _renderSplitOutputWithHeader($header, $body)
	{
		return '<h3 class="report-split-header">' . $header . '</h3>' . "\n$body";
	}

	/**
	 * Renders a table with the specified rows/data.
	 *
	 * @param array $rows
	 *
	 * @return string
	 */
	protected function _renderTable(array $rows)
	{
		if (!$rows) {
			return '';
		}

		if ($this->_handler->getGroupXColumns()) {
			return $this->_renderMatrixTable($rows);
		}

		return $this->_renderTableWrapper($this->_renderHeader($rows) . $this->_renderBody($rows));
	}

	/**
	 * Renders the outer table wrapper.
	 *
	 * @param string $inner Content inside table
	 * @param string $extraClass Any extra classes to add (space separated)
	 *
	 * @return string
	 */
	protected function _renderTableWrapper($inner, $extraClass = '')
	{
		return "<table class=\"report-builder-table $extraClass\">\n$inner\n</table>\n";
	}

	/**
	 * Renders the header row (for a simple table).
	 *
	 * @param array $rows
	 *
	 * @return string
	 */
	protected function _renderHeader(array $rows)
	{
		$columnHtml = array();
		foreach ($this->_handler->getGroupYColumns() AS $column) {
			$columnHtml[] = '<th>' . $this->_valueRenderer->escapeValue($column['title']) . '</th>';
		}
		foreach ($this->_handler->getSelectColumns() AS $column) {
			$columnHtml[] = '<th>' . $this->_valueRenderer->escapeValue($column['title']) . '</th>';
		}

		return '<thead><tr class="row-header">' . implode("\n\t", $columnHtml) . '</tr></thead>';
	}

	/**
	 * Renders the body of a "simple" table.
	 *
	 * @param array $rows
	 *
	 * @return string
	 */
	protected function _renderBody(array $rows)
	{
		$groupColumns = $this->_handler->getGroupYColumns();
		$selectColumns = $this->_handler->getSelectColumns();
		$rows = array_values($rows); // need continuous keys

		$rowsHtml = array();
		$rowCount = 0;

		$groupSkipCount = array();
		foreach ($groupColumns AS $groupId => $groupColumn) {
			$groupSkipCount[$groupId] = 0;
		}

		foreach ($rows AS $rowId => $row) {
			$cells = array();

			if ($groupColumns) {
				$myGroupSkipCount = $groupSkipCount;

				$groupValues = array();
				foreach ($groupColumns AS $groupId => $groupColumn) {
					$groupValues[$groupId] = $this->_getColumnValue($row, $groupColumn['groupResultId']);
				}

				$nextRowId = $rowId + 1;
				if (isset($rows[$nextRowId])) {
					$firstNonMatch = null;
					for (; isset($rows[$nextRowId]); $nextRowId++) {
						$nextRow = $rows[$nextRowId];
						$matched = 0;

						foreach ($groupColumns AS $groupId => $groupColumn) {
							if ($firstNonMatch !== null && $firstNonMatch == $groupId) {
								// can't go any further as this column doesn't match from before
								break;
							}

							$groupValue = $this->_getColumnValue($nextRow, $groupColumn['groupResultId']);
							if ($groupValues[$groupId] == $groupValue) {
								$matched++;
								if (!$myGroupSkipCount[$groupId]) {
									// if there's a skip count for this, we don't need to increase it
									// as it's already been accounted for
									$groupSkipCount[$groupId]++;
								}
							} else {
								$firstNonMatch = $groupId;
								break;
							}
						}

						if (!$matched) {
							break;
						}
					}
				}

				foreach ($groupColumns AS $groupId => $groupColumn) {
					if ($myGroupSkipCount[$groupId]) {
						$groupSkipCount[$groupId]--;
						continue;
					}

					$rowSpan = ($groupSkipCount[$groupId]
						? ' rowspan="' . ($groupSkipCount[$groupId] + 1) . '"'
						: ''
					);
					$rendered = $this->_renderCellValue($row, $groupColumn);

					$cells[] = "<th$rowSpan>$rendered</th>";
				}
			}

			foreach ($selectColumns AS $column) {
				$cells[] = '<td>' . $this->_renderCellValue($row, $column) . '</td>';
			}

			$rowCount++;
			$class = ($rowCount % 2 ? 'odd' : 'even');

			$rowsHtml[] = '<tr class="row-body ' . $class . '">' . implode("\n\t", $cells) . '</tr>';
		}

		if ($rowsHtml) {
			return '<tbody>' . implode("\n", $rowsHtml) . '</tbody>';
		} else {
			return '';
		}
	}

	/**
	 * Renders a matrix table (with X and Y grouping).
	 *
	 * @param array $rows
	 *
	 * @return string
	 */
	protected function _renderMatrixTable(array $rows)
	{
		$prepared = $this->_prepareMatrixTable($rows);

		return $this->_renderTableWrapper(
			$this->_renderMatrixHeader($prepared) . $this->_renderMatrixBody($prepared),
			'matrix'
		);
	}

	/**
	 * Renders the header rows of a matrix table.
	 *
	 * @param array $prepared Prepared matrix data (see _prepareMatrixTable).
	 *
	 * @return string
	 */
	protected function _renderMatrixHeader(array $prepared)
	{
		$rowSkipCount = count($this->_handler->getGroupXColumns());
		$colSkipCount = count($this->_handler->getGroupYColumns());

		$header = $this->_renderMatrixHeaderRecur(array('root'), $prepared['xDistinct']);
		$rows = $header['depth'];
		ksort($rows);

		$output = array();
		foreach ($rows AS $depth => $row) {
			if ($depth === 0 && $colSkipCount) {
				$colSpan = ($colSkipCount > 1 ? " colspan=\"$colSkipCount\"" : '');
				$rowSpan = ($rowSkipCount > 1 ? " rowspan=\"$rowSkipCount\"" : '');
				$row = "<th$colSpan$rowSpan>&nbsp;</th>" . $row;
			}
			$output[] = "<tr class=\"row-header\">$row</tr>";
		}

		if ($output) {
			return '<thead>' . implode("\n\t", $output) . '</thead>';
		} else {
			return '';
		}
	}

	/**
	 * Internal helper to render matrix table header rows.
	 *
	 * Returns array with keys:
	 *  - colSpan -- number of columns spanned by children
	 *  - depth -- array of HTML for each depth below this one
	 *
	 * @param array $path Grouping path
	 * @param array $distinctValues
	 * @param int $depth
	 *
	 * @return array
	 */
	protected function _renderMatrixHeaderRecur(array $path, array $distinctValues, $depth = 0)
	{
		$pathLookup = $this->_getGroupPathKey($path);
		if (!isset($distinctValues[$pathLookup])) {
			return array('colSpan' => 0, 'depth' => array());
		}

		$colSpan = 0;
		$siblings = array();

		$nextDepth = $depth + 1;
		$depthHtml = array();

		foreach ($distinctValues[$pathLookup] AS $groupValue => $printValue) {
			$localPath = $path;
			$localPath[] = $groupValue;

			$child = $this->_renderMatrixHeaderRecur($localPath, $distinctValues, $nextDepth);

			foreach ($child['depth'] AS $level => $childDepthHtml) {
				if (!isset($depthHtml[$level])) {
					$depthHtml[$level] = '';
				}
				$depthHtml[$level] .= $childDepthHtml;
			}

			$colSpan += max(1, $child['colSpan']);

			$colSpanHtml = ($child['colSpan'] > 1 ? ' colspan="' . $child['colSpan'] . '"' : '');
			$valueHtml = "<th$colSpanHtml>$printValue</th>";

			$siblings[] = $valueHtml;
		}

		$depthHtml[$depth] = implode('', $siblings);

		return array(
			'colSpan' => $colSpan,
			'depth' => $depthHtml
		);
	}

	/**
	 * Renders the body of a matrix table.
	 *
	 * @param array $prepared Prepared matrix data
	 *
	 * @return string
	 */
	protected function _renderMatrixBody(array $prepared)
	{
		if (!$prepared['yDistinct']) {
			// no Y grouping - that means we can have one row so fake it
			$rowKeys = array('root' => '');
		} else {
			$rowKeys = $this->_getMatrixRowGroups(array('root'), $prepared['yDistinct']);
		}

		$matrixPaths = $this->_getFinalMatrixPaths(array('root'), $prepared['xDistinct']);
		$lookup = $prepared['lookup'];

		$rows = array();
		$rowCount = 0;

		foreach ($rowKeys AS $yPath => $html) {
			$cells = array();
			foreach ($matrixPaths AS $xPath) {
				if (isset($lookup[$yPath][$xPath])) {
					$value = $lookup[$yPath][$xPath];
				} else {
					$value = '';
				}
				$cells[] = "<td>$value</td>";
			}

			$rowCount++;
			$class = ($rowCount % 2 ? 'odd' : 'even');

			$rows[] = '<tr class="row-body ' . $class . '">' . $html . implode('', $cells) . '</tr>';
		}

		if ($rows) {
			return '<tbody>' . implode("\n\t", $rows) . '</tbody>';
		} else {
			return '';
		}
	}

	/**
	 * Gets the groupings that will represent rows in a matrix tables, including
	 * ultimate Y paths.
	 *
	 * @param array $path Grouping path to this point
	 * @param array $yDistinct Distinct values in the Y direction
	 *
	 * @return array HTML for each unique row of Y grouping columns
	 */
	protected function _getMatrixRowGroups(array $path, array $yDistinct)
	{
		$pathString = $this->_getGroupPathKey($path);
		if (!isset($yDistinct[$pathString])) {
			return array();
		}

		$output = array();
		foreach ($yDistinct[$pathString] AS $groupValue => $printValue) {
			$localPath = $path;
			$localPath[] = $groupValue;

			$children = $this->_getMatrixRowGroups($localPath, $yDistinct);
			if (!$children) {
				$output[$this->_getGroupPathKey($localPath)] = '<th>' . $printValue . '</th>';
			} else {
				$rowSpan = count($children);
				$rowSpanHtml = ($rowSpan > 1 ? " rowspan=\"$rowSpan\"" : '');

				$first = '<th' . $rowSpanHtml . '>' . $printValue . '</th>';

				foreach ($children AS $key => $child) {
					$output[$key] = $first . $child;
					$first = '';
				}
			}
		}

		return $output;
	}

	/**
	 * Renders a chart with the specified rows/data.
	 *
	 * @param string $type Type of chart (bar, line, pie)
	 * @param array $rows
	 *
	 * @return string|bool
	 */
	protected function _renderChart($type, array $rows)
	{
		$optionMap = array(
			'bar' => '
				graph.type = "column";
				graph.fillAlphas = 1;
			',
			'line' => '
				graph.type = "line";
				graph.lineThickness = 2;
				graph.bullet = "round";
			',
			'pie' => ''
		);
		if (!isset($optionMap[$type])) {
			return false;
		}

		if (!$rows) {
			return '';
		}

		$originalValueRenderer = $this->_valueRenderer;
		$this->_valueRenderer = new \Application\DeskPRO\Dpql\Renderer\Values\Text();

		$selectColumns = $this->_handler->getSelectColumns();
		$groupYColumns = $this->_handler->getGroupYColumns();
		$groupXColumns = $this->_handler->getGroupXColumns();

		$chartData = array();
		$graphs = array();

		if ($groupXColumns) {
			// matrix table - X() values translate to bottom axis, each row (from Y()) is a new line/bar.
			$prepared = $this->_prepareMatrixTable($rows);
			$lookup = $prepared['lookup'];

			$rowGroups = $this->_getFinalMatrixPathsWithPrintable(array('root'), $prepared['yDistinct']);
			if (!$rowGroups) {
				// need to fake it so we get a row with no Y grouping
				$rowGroups = array('root' => array());
			}
			$headerCols = $this->_getFinalMatrixPathsWithPrintable(array('root'), $prepared['xDistinct']);

			foreach ($headerCols AS $xPath => $printable) {
				$rowData = array('category' => implode(' / ', $printable));

				$i = 0;
				foreach ($rowGroups AS $yPath => $null) {
					if (isset($lookup[$yPath][$xPath])) {
						$value = $this->_filterGraphValue($lookup[$yPath][$xPath]);
					} else {
						$value = '';
					}
					$rowData['value' . $i] = $value;
					$i++;
				}

				$chartData[] = $rowData;
			}

			$i = 0;
			foreach ($rowGroups AS $printable) {
				$graphs[$i] = array(
					'title' => implode(' / ', $printable),
					'value' => "value$i"
				);
				$i++;
			}

			$hasCategory = true;
		} else {
			foreach ($rows AS $row) {
				$categories = array();
				foreach ($groupYColumns AS $column) {
					$categories[] = $this->_renderCellValue($row, $column);
				}
				$category = implode(' / ', $categories);

				$rowData = array('category' => $category);

				foreach ($selectColumns AS $i => $column) {
					$rowData['value' . $i] = $this->_filterGraphValue($this->_renderCellValue($row, $column));
				}

				$chartData[] = $rowData;
			}

			foreach ($selectColumns AS $i => $column) {
				$graphs[$i] = array(
					'title' => $column['title'],
					'value' => "value$i"
				);
			}

			$hasCategory = count($groupYColumns) > 0;
		}

		if ($type == 'pie') {
			$output = '';

			$sliceCount = count($chartData);
			$height = 400 + ceil($sliceCount / 4) * 20;

			foreach ($graphs AS $i => $graph) {
				$id = 'report_chart_' . md5(uniqid());

				$output .= '
					<div id="' . $id . '" class="report-chart" style="height: ' . $height . 'px"></div>
					<script type="text/javascript">
					$(function() {
						var chart = new AmCharts.AmPieChart();
						chart.dataProvider = ' . json_encode($chartData) . ';
						chart.titleField = "category";
						chart.valueField = "' . $graph['value'] . '";
						chart.startDuration = 0;
						' . ($sliceCount >= 25 ? 'chart.labelsEnabled = false;' : '') . '
						chart.addLegend(new AmCharts.AmLegend());

						chart.write("' . $id . '");
					});
					</script>
				';
			}
		} else {
			if ($hasCategory) {
				$balloonText = '[[category]], [[title]]: [[value]]';
			} else {
				$balloonText = '[[title]]: [[value]]';
			}

			$graphCode = array();
			foreach ($graphs AS $graph) {
				$graphCode[] = '
					graph = new AmCharts.AmGraph();
					graph.valueField = "' . $graph['value'] . '";
					graph.title = "' . $this->_jsEscapeValue($graph['title']) . '";
					graph.balloonText = "' . $balloonText .'";
					' . $optionMap[$type] . '
					chart.addGraph(graph);
				';
			}

			$id = 'report_chart_' . md5(uniqid());
			$output = '
				<div id="' . $id . '" class="report-chart"></div>
				<script type="text/javascript">
				$(function() {
					var chart = new AmCharts.AmSerialChart();
					chart.dataProvider = ' . json_encode($chartData) . ';
					chart.categoryField = "category";
					chart.addLegend(new AmCharts.AmLegend());

					var graph;
					' . implode("\n", $graphCode) . '

					chart.write("' . $id . '");
				});
				</script>
			';
		}

		$this->_valueRenderer = $originalValueRenderer;
		return $output;
	}

	protected function _jsEscapeValue($value)
	{
		return strtr($value, array(
			'"' => '\\"',
			"'" => "\\'",
			'\\' => '\\\\',
			'</script>' => '<\\/script>'
		));
	}

	/**
	 * Filters a graph value that looks like a number into an actual number.
	 *
	 * @param string $value
	 *
	 * @return string|number
	 */
	protected function _filterGraphValue($value)
	{
		if (preg_match('/^((\d+,)*\d+)(\.\d+)?%?$/', $value)) {
			return str_replace(array(',', '%'), '', $value) + 0;
		} else {
			return $value;
		}
	}
}