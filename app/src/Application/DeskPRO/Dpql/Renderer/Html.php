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
class Html
{
	/**
	 * Result handler that stores all the bits that will be displayed/formatted.
	 *
	 * @var \Application\DeskPRO\Dpql\ResultHandler
	 */
	protected $_handler;

	/**
	 * Results to render.
	 *
	 * @var \Application\DeskPRO\Dpql\Results
	 */
	protected $_results;

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
	 * @param \Application\DeskPRO\Dpql\ResultHandler $resultHandler
	 * @param \Application\DeskPRO\Dpql\Results $results
	 */
	public function __construct(ResultHandler $resultHandler, Results $results)
	{
		$this->_handler = $resultHandler;
		$this->_results = $results;
	}

	/**
	 * Render to the specified format (in this case HTML)
	 *
	 * @return string
	 */
	public function render()
	{
		$splitColumns = $this->_handler->getSplitColumns();

		if ($splitColumns) {
			$output = array();
			foreach ($this->_results->getSplitResults() AS $splitResult) {
				$table = $this->renderSplitTable($splitResult);
				if ($table) {
					$output[] = $table;
				}
			}

			return implode("\n\n", $output);
		} else {
			return $this->renderTable($this->_results->getResults());
		}
	}

	/**
	 * Renders results with a SPLIT clause into however many tables
	 * are needed.
	 *
	 * @param array $splitResults Key 0 is rows in table, 1 is columns in split query
	 *
	 * @return string
	 */
	public function renderSplitTable(array $splitResult)
	{
		$splitColumns = $this->_handler->getSplitColumns();

		$table = $this->renderTable($splitResult[0]);
		if (!$table) {
			return '';
		}

		$splitPrint = array();
		foreach ($this->_handler->getSplitColumns() AS $splitColumn) {
			$splitPrint[] = $this->_renderCellValue($splitResult[1], $splitColumn);
		}

		return $this->renderSplitHeader(implode(' / ', $splitPrint)) . "\n" . $table;
	}

	/**
	 * Renders the header of a split table
	 *
	 * @param string $title
	 *
	 * @return string
	 */
	public function renderSplitHeader($title)
	{
		return '<h3 class="report-split-header">' . $title . '</h3>';
	}

	/**
	 * Renders a table with the specified rows/data.
	 *
	 * @param array $rows
	 *
	 * @return string
	 */
	public function renderTable(array $rows)
	{
		if (!$rows) {
			return '';
		}

		if ($this->_handler->getGroupXColumns()) {
			return $this->_renderMatrixTable($rows);
		}

		return $this->_renderTableTag($this->_renderHeader($rows) . $this->_renderBody($rows));
	}

	/**
	 * Renders the outer table tag.
	 *
	 * @param string $inner HTML inside table
	 * @param string $extraClass Any extra classes to add (space separated)
	 *
	 * @return string
	 */
	protected function _renderTableTag($inner, $extraClass = '')
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
			$columnHtml[] = '<th>' . htmlspecialchars($column['title']) . '</th>';
		}
		foreach ($this->_handler->getSelectColumns() AS $column) {
			$columnHtml[] = '<th>' . htmlspecialchars($column['title']) . '</th>';
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
	 * Renders the value for a specific cell.
	 *
	 * @param mixed[int] $row
	 * @param array $column
	 *
	 * @return string
	 */
	protected function _renderCellValue(array $row, array $column)
	{
		$renderer = $column['renderer'];
		$value = $column['resultId'] ? $row[$column['resultId'] - 1] : '';

		if ($renderer instanceof \Closure) {
			return $renderer('html', $value, $row, $this);
		}

		return $this->renderValue($value, $renderer);
	}

	public function renderValue($value, $format)
	{
		if ($value === null) {
			return $this->_renderNull();
		}

		switch ($format) {
			case 'boolean':
				return $this->_renderBoolean($value);

			case 'datetime':
			case 'date':
			case 'time':
				$settingMap = array(
					'datetime' => 'core.date_fulltime',
					'date' => 'core.date_full',
					'time' => 'core.date_time'
				);

				$tz = App::getCurrentPerson()->getTimezone();
				try {
					$date = new \DateTime($value, new \DateTimeZone($tz));
					return $date->format(App::getSetting($settingMap[$format]));
				} catch (\Exception $e) {
					return $this->escapeValue($value);
				}

			case 'string':
			default:
				return $this->escapeValue($value);
		}
	}

	protected function _renderNull()
	{
		return '<span class="null">None</span>';
	}

	protected function _renderBoolean($value)
	{
		if ($value) {
			return '<span class="true">Y</span>';
		} else {
			return '<span class="false">N</span>';
		}
	}

	public function escapeValue($value)
	{
		return htmlspecialchars($value);
	}

	/**
	 * Gets the value of a particular column for the given row.
	 *
	 * @param array $row
	 * @param integer $id
	 *
	 * @return string
	 */
	protected function _getColumnValue(array $row, $id)
	{
		return ($id ? $row[$id - 1] : '');
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

		return $this->_renderTableTag(
			$this->_renderMatrixHeader($prepared) . $this->_renderMatrixBody($prepared),
			'matrix'
		);
	}

	/**
	 * Prepares data for a matrix table.
	 *
	 * Returns array with:
	 *  - xDistinct[pathString][renderedValue] = true -- used to find distinct values over X grouping
	 *  - yDistinct[pathString][renderedValue] = true -- used to find distinct values over Y grouping
	 *  - lookup[yPath][xPath] = cell value -- value for cell at the y/x position specified
	 *
	 * @param array $rows
	 *
	 * @return array
	 */
	protected function _prepareMatrixTable(array $rows)
	{
		$groupXColumns = $this->_handler->getGroupXColumns();
		$groupYColumns = $this->_handler->getGroupYColumns();
		$selectColumns = $this->_handler->getSelectColumns();

		$distinctXValues = array();
		$distinctYValues = array();
		$lookup = array();

		foreach ($rows AS $row) {
			$xPath = array('root');
			foreach ($groupXColumns AS $column) {
				$pathString = $this->_getGroupPathKey($xPath);
				$groupValue = $this->_getColumnValue($row, $column['groupResultId']);

				$distinctXValues[$pathString][$groupValue] = $this->_renderCellValue($row, $column);

				$xPath[] = $groupValue;
			}

			$yPath = array('root');
			foreach ($groupYColumns AS $column) {
				$pathString = $this->_getGroupPathKey($yPath);
				$groupValue = $this->_getColumnValue($row, $column['groupResultId']);

				$distinctYValues[$pathString][$groupValue] = $this->_renderCellValue($row, $column);

				$yPath[] = $groupValue;
			}

			$lookup[$this->_getGroupPathKey($yPath)][$this->_getGroupPathKey($xPath)] =
				$this->_renderMatrixCell($row, $selectColumns);
		}

		return array(
			'xDistinct' => $distinctXValues,
			'yDistinct' => $distinctYValues,
			'lookup' => $lookup
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
	 * Gets the final path keys to a set of distinct values in a matrix table.
	 *
	 * @param array $path Grouping paths
	 * @param array $distinct Distinct values
	 *
	 * @return array List of path keys
	 */
	protected function _getFinalMatrixPaths(array $path, array $distinct)
	{
		$pathString = $this->_getGroupPathKey($path);
		if (!isset($distinct[$pathString])) {
			return array();
		}

		$output = array();
		foreach ($distinct[$pathString] AS $value => $null) {
			$localPath = $path;
			$localPath[] = $value;

			$children = $this->_getFinalMatrixPaths($localPath, $distinct);
			if (!$children) {
				$output[] = $this->_getGroupPathKey($localPath);
			} else {
				$output = array_merge($output, $children);
			}
		}

		return $output;
	}

	/**
	 * Renders a matrix cell.
	 *
	 * @param array $row
	 * @param array $selectColumns
	 *
	 * @return string
	 */
	protected function _renderMatrixCell(array $row, array $selectColumns)
	{
		$values = array();
		foreach ($selectColumns AS $column) {
			$values[] = $this->_renderCellValue($row, $column);
		}

		return implode(' / ', $values);
	}

	/**
	 * Gets the string key to identify a path to a value based on parts.
	 *
	 * @param array $groupParts
	 *
	 * @return string
	 */
	protected function _getGroupPathKey(array $groupParts)
	{
		return implode('|', $groupParts);
	}
}