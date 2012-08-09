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
				$splitId[] = $this->renderCell($row, $column);
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

	public function renderBody(array $rows)
	{
		$rowsHtml = array();
		foreach ($rows AS $row) {
			$rowsHtml[] = $this->renderRow($row);
		}

		return implode("\n", $rowsHtml);
	}

	public function renderRow(array $row)
	{
		$cells = array();
		foreach ($this->_handler->getGroupYColumns() AS $column) {
			$cells[] = '<td>' . $this->renderCell($row, $column) . '</td>';
		}
		foreach ($this->_handler->getSelectColumns() AS $column) {
			$cells[] = '<td>' . $this->renderCell($row, $column) . '</td>';
		}

		if ($cells) {
			return '<tr>' . implode("\n\t", $cells) . '</tr>';
		} else {
			return '';
		}
	}

	public function renderCell(array $row, array $column)
	{
		$renderer = $column['render'];

		if (is_int($renderer)) {
			return htmlspecialchars($row[$renderer - 1]);
		} else {
			return $renderer;
		}
	}
}