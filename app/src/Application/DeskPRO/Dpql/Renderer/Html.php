<?php

namespace Application\DeskPRO\Dpql\Renderer;

use Application\DeskPRO\Dpql\ResultHandler;

class Html
{
	protected $_handler;
	protected $_results;

	public function __construct(ResultHandler $resultHandler, array $results)
	{
		$this->_handler = $resultHandler;
		$this->_results = $results;
	}

	public function render()
	{
		$splitColumns = $this->_handler->getSplitColumns();

		if ($splitColumns) {
			return $this->renderSplitTable($splitColumns);
		} else {
			return $this->renderTable($this->_results);
		}
	}

	public function renderSplitTable(array $splitColumns)
	{
		$splitResults = array();

		foreach ($this->_results AS $key => $row) {
			$splitId = array();
			foreach ($splitColumns AS $column) {
				$splitId[] = $this->_renderCell($row, $column);
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
		return '<table border=1>'
			. $this->renderHeader()
			. $this->renderBody($rows)
			. '</table>';
	}

	public function renderHeader()
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

	protected $_rowSpans = array();
	protected $_rowGroupHit = array();

	public function renderBody(array $rows)
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
					$groupParts[] = $this->_renderCell($row, $column);
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
			$rendered =  $this->_renderCell($row, $column);
			$groupParts[] = $rendered;

			$groupPath = $this->_getGroupPathKey($groupParts);

			if (empty($this->_rowGroupHit[$groupPath])) {
				$this->_rowGroupHit[$groupPath] = true;

				$rowSpan = ($this->_rowSpans[$groupPath] > 1
					? ' rowspan="' . $this->_rowSpans[$groupPath] . '"'
					: ''
				);

				$cells[] = "<td$rowSpan>$rendered</td>";
			}
		}

		foreach ($this->_handler->getSelectColumns() AS $column) {
			$cells[] = '<td>' . $this->_renderCell($row, $column) . '</td>';
		}

		if ($cells) {
			return '<tr>' . implode("\n\t", $cells) . '</tr>';
		} else {
			return '';
		}
	}

	protected function _renderCell(array $row, array $column)
	{
		$renderer = $column['render'];

		if (is_int($renderer)) {
			return htmlspecialchars($row[$renderer - 1]);
		} else {
			return $renderer;
		}
	}

	protected function _getGroupPathKey(array $groupParts)
	{
		return implode('|', $groupParts);
	}
}