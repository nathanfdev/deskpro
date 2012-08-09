<?php

namespace Application\DeskPRO\Dpql\Renderer;

use Application\DeskPRO\Dpql\ResultHandler;

class Html
{
	protected $_handler;
	protected $_results;

	protected $_rowSpans = array();
	protected $_rowGroupHit = array();

	public function __construct(ResultHandler $resultHandler, array $results)
	{
		$this->_handler = $resultHandler;
		$this->_results = $results;
	}

	public function render()
	{
		$splitColumns = $this->_handler->getSplitColumns();

		if ($splitColumns) {
			return $this->renderSplitTable($this->_results);
		} else {
			return $this->renderTable($this->_results);
		}
	}

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

	public function renderSplitHeader($title)
	{
		return '<h3>' . $title . '</h3>';
	}

	public function renderTable(array $rows)
	{
		if ($this->_handler->getGroupXColumns()) {
			return $this->_renderMatrixTable($rows);
		}

		return $this->_renderTableTag($this->_renderHeader($rows) . $this->_renderBody($rows));
	}

	protected function _renderTableTag($inner)
	{
		return "<table border=1>\n$inner\n</table>\n";
	}

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

	protected function _renderBody(array $rows)
	{
		$this->_analyzeRows($rows);

		$rowsHtml = array();
		foreach ($rows AS $row) {
			$rowsHtml[] = $this->_renderRow($row);
		}

		return implode("\n", $rowsHtml);
	}

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

	protected function _renderCellValue(array $row, array $column)
	{
		$renderer = $column['render'];

		if (is_int($renderer)) {
			return htmlspecialchars($row[$renderer - 1]);
		} else {
			return $renderer;
		}
	}

	protected function _renderMatrixTable(array $rows)
	{
		$prepared = $this->_prepareMatrixTable($rows);

		return $this->_renderTableTag(
			$this->_renderMatrixHeader($prepared)
			. $this->_renderMatrixBody($prepared)
		);
	}

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

	protected function _renderMatrixBody(array $prepared)
	{
		$rowKeys = $this->_getMatrixRowGroups(array('root'), $prepared['yDistinct']);
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

	protected function _renderMatrixCell(array $row, array $selectColumns)
	{
		$values = array();
		foreach ($selectColumns AS $column) {
			$values[] = $this->_renderCellValue($row, $column);
		}

		return implode(' / ', $values);
	}

	protected function _getGroupPathKey(array $groupParts)
	{
		return implode('|', $groupParts);
	}
}