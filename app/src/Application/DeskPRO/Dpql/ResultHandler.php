<?php

namespace Application\DeskPRO\Dpql;

class ResultHandler
{
	protected $_columns = array();
	protected $_groupXColumns = array();
	protected $_groupYColumns = array();
	protected $_splitColumns = array();

	public function addSelectColumn($title, $render)
	{
		$this->_columns[] = array(
			'title' => $title,
			'render' => $render
		);
	}

	public function getSelectColumns()
	{
		return $this->_columns;
	}

	public function addGroupYColumn($title, $render)
	{
		$this->_groupYColumns[] = array(
			'title' => $title,
			'render' => $render
		);
	}

	public function addGroupXColumn($title, $render)
	{
		$this->_groupXColumns[] = array(
			'title' => $title,
			'render' => $render
		);
	}

	public function addSplitColumn($title, $render)
	{
		$this->_splitColumns[] = array(
			'title' => $title,
			'render' => $render
		);
	}
}