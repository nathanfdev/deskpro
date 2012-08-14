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
	 * Results to render. First dimension is rows, second dimension are columns
	 * in the row (with numbered, 1-based keys).
	 *
	 * @var mixed[mixed][int]
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
	 * @param mixed[mixed][int] $results
	 */
	public function __construct(ResultHandler $resultHandler, array $results)
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
			return $this->renderSplitTable($this->_results);
		} else {
			return $this->renderTable($this->_results);
		}
	}

	/**
	 * Renders results with a SPLIT clause into however many tables
	 * are needed.
	 *
	 * @param mixed[mixed][int] $rows
	 *
	 * @return string
	 */
	public function renderSplitTable(array $rows)
	{
		$splitColumns = $this->_handler->getSplitColumns();
		$splitResults = array();

		foreach ($rows AS $key => $row) {
			$splitId = array();
			foreach ($splitColumns AS $column) {
				$splitId[] = $this->_renderCellValue($row, $column);
			}
			$splitResults[implode(' / ', $splitId)][$key] = $row;
		}

		$output = '';
		foreach ($splitResults AS $splitTitle => $splitResult) {
			$output .= $this->renderSplitHeader($splitTitle)
				. "\n" . $this->renderTable($splitResult);
		}

		return $output;
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
		return '<h3>' . $title . '</h3>';
	}

	/**
	 * Renders a table with the specified rows/data.
	 *
	 * @param mixed[mixed][int] $rows
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
	 *
	 * @return string
	 */
	protected function _renderTableTag($inner)
	{
		return "<table class=\"report-builder-table\">\n$inner\n</table>\n";
	}

	/**
	 * Renders the header row (for a simple table).
	 *
	 * @param mixed[mixed][int] $rows
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

		return '<tr>' . implode("\n\t", $columnHtml) . '</tr>';
	}

	/**
	 * Renders the body of a "simple" table.
	 *
	 * @param mixed[mixed][int] $rows
	 *
	 * @return string
	 */
	protected function _renderBody(array $rows)
	{
		$this->_analyzeRows($rows);

		$rowsHtml = array();
		foreach ($rows AS $row) {
			$rowsHtml[] = $this->_renderRow($row);
		}

		return implode("\n", $rowsHtml);
	}

	/**
	 * Analyzes the rows of a "simple" table to determine row spans.
	 *
	 * @param mixed[mixed][int] $rows
	 */
	protected function _analyzeRows(array $rows)
	{
		$this->_rowSpans = array();
		$this->_rowGroupHit = array();

		$groupColumns = $this->_handler->getGroupYColumns();
		if ($groupColumns) {
			$rowSpans = array();

			foreach ($rows AS $row) {
				$groupParts = array();
				foreach ($groupColumns AS $column) {
					$groupParts[] = $this->_renderCellValue($row, $column);
					$groupPath = $this->_getGroupPathKey($groupParts);

					if (isset($rowSpans[$groupPath])) {
						$rowSpans[$groupPath]++;
					} else {
						$rowSpans[$groupPath] = 1;
					}
				}
			}

			$this->_rowSpans = $rowSpans;
		}
	}

	/**
	 * Renders the given row for a "simple" table.
	 *
	 * @param mixed[int] $row
	 *
	 * @return string
	 */
	protected function _renderRow(array $row)
	{
		$groupParts = array();
		$cells = array();

		foreach ($this->_handler->getGroupYColumns() AS $column) {
			$rendered = $this->_renderCellValue($row, $column);
			$groupParts[] = $rendered;

			$groupPath = $this->_getGroupPathKey($groupParts);

			if (empty($this->_rowGroupHit[$groupPath])) {
				$this->_rowGroupHit[$groupPath] = true;

				$rowSpan = ($this->_rowSpans[$groupPath] > 1
					? ' rowspan="' . $this->_rowSpans[$groupPath] . '"'
					: ''
				);

				$cells[] = "<th$rowSpan>$rendered</th>";
			}
		}

		foreach ($this->_handler->getSelectColumns() AS $column) {
			$cells[] = '<td>' . $this->_renderCellValue($row, $column) . '</td>';
		}

		if ($cells) {
			return '<tr>' . implode("\n\t", $cells) . '</tr>';
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

		if (!$renderer) {
			return htmlspecialchars($value);
		} else if ($renderer instanceof \Closure) {
			return $renderer('html', $value);
		} else {
			return htmlspecialchars($renderer);
		}
	}

	/**
	 * Renders a matrix table (with X and Y grouping).
	 *
	 * @param mixed[mixed][int] $rows
	 *
	 * @return string
	 */
	protected function _renderMatrixTable(array $rows)
	{
		$prepared = $this->_prepareMatrixTable($rows);

		return $this->_renderTableTag(
			$this->_renderMatrixHeader($prepared)
			. $this->_renderMatrixBody($prepared)
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
	 * @param mixed[mixed][int] $rows
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
				$rendered = $this->_renderCellValue($row, $column);

				$distinctXValues[$pathString][$rendered] = true;

				$xPath[] = $rendered;
			}

			$yPath = array('root');
			foreach ($groupYColumns AS $column) {
				$pathString = $this->_getGroupPathKey($yPath);
				$rendered = $this->_renderCellValue($row, $column);

				$distinctYValues[$pathString][$rendered] = true;

				$yPath[] = $rendered;
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
			$output[] = "<tr>$row</tr>";
		}

		return implode("\n\t", $output);
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

		foreach ($distinctValues[$pathLookup] AS $value => $null) {
			$localPath = $path;
			$localPath[] = $value;

			$child = $this->_renderMatrixHeaderRecur($localPath, $distinctValues, $nextDepth);

			foreach ($child['depth'] AS $level => $childDepthHtml) {
				if (!isset($depthHtml[$level])) {
					$depthHtml[$level] = '';
				}
				$depthHtml[$level] .= $childDepthHtml;
			}

			$colSpan += max(1, $child['colSpan']);

			$colSpanHtml = ($child['colSpan'] > 1 ? ' colspan="' . $child['colSpan'] . '"' : '');
			$valueHtml = "<th$colSpanHtml>$value</th>";

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

			$rows[] = '<tr>' . $html . implode('', $cells) . '</tr>';
		}

		return implode("\n\t", $rows);
	}

	/**
	 * Gets the groupings that will represent rows in a matrix tables, including
	 * ultimate Y paths.
	 *
	 * @param array $path Grouping path to this point
	 * @param array $yDistinct Distinct values in the Y direction
	 *
	 * @return array[string] HTML for each unique row of Y grouping columns
	 */
	protected function _getMatrixRowGroups(array $path, array $yDistinct)
	{
		$pathString = $this->_getGroupPathKey($path);
		if (!isset($yDistinct[$pathString])) {
			return array();
		}

		$output = array();
		foreach ($yDistinct[$pathString] AS $value => $null) {
			$localPath = $path;
			$localPath[] = $value;

			$children = $this->_getMatrixRowGroups($localPath, $yDistinct);
			if (!$children) {
				$output[$this->_getGroupPathKey($localPath)] = '<th>' . htmlspecialchars($value) . '</th>';
			} else {
				$rowSpan = count($children);
				$rowSpanHtml = ($rowSpan > 1 ? " rowspan=\"$rowSpan\"" : '');

				$first = '<th' . $rowSpanHtml . '>' . htmlspecialchars($value) . '</th>';

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