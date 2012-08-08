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
		return '<table border=1>'
			. $this->renderHeader()
			. $this->renderBody()
			. '</table>';
	}

	public function renderHeader()
	{
		$columnHtml = array();
		foreach ($this->_handler->getSelectColumns() AS $column) {
			$columnHtml[] = '<th>' . htmlspecialchars($column['title']) . '</th>';
		}

		return '<tr>' . implode("\n\t", $columnHtml) . '</tr>';
	}

	public function renderBody()
	{
		$rows = array();
		foreach ($this->_results AS $row) {
			$rows[] = $this->renderRow($row);
		}

		return implode("\n", $rows);
	}

	public function renderRow(array $row)
	{
		$cells = array();
		foreach ($this->_handler->getSelectColumns() AS $column) {
			$cells[] = $this->renderCell($row, $column);
		}

		if ($cells) {
			return '<tr><td>' . implode("</td>\n\t<td>", $cells) . '</td></tr>';
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