<?php

namespace Application\DeskPRO\Dpql;

class ResultHandler
{
	protected $_columns = array();
	protected $_groupXColumns = array();
	protected $_groupYColumns = array();
	protected $_splitColumns = array();

	public function addSelectColumn($title, $resultId, $renderer = null)
	{
		$this->_columns[] = array(
			'title' => $title,
			'resultId' => $resultId,
			'renderer' => $renderer
		);
	}

	public function getSelectColumns()
	{
		return $this->_columns;
	}

	public function addGroupYColumn($title, $resultId, $renderer = null)
	{
		$this->_groupYColumns[] = array(
			'title' => $title,
			'resultId' => $resultId,
			'renderer' => $renderer
		);
	}

	public function getGroupYColumns()
	{
		return $this->_groupYColumns;
	}

	public function addGroupXColumn($title, $resultId, $renderer = null)
	{
		$this->_groupXColumns[] = array(
			'title' => $title,
			'resultId' => $resultId,
			'renderer' => $renderer
		);
	}

	public function getGroupXColumns()
	{
		return $this->_groupXColumns;
	}

	public function addSplitColumn($resultId, $renderer = null)
	{
		$this->_splitColumns[] = array(
			'resultId' => $resultId,
			'renderer' => $renderer
		);
	}

	public function getSplitColumns()
	{
		return $this->_splitColumns;
	}
}