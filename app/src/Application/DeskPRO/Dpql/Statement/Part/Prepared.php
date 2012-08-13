<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

class Prepared
{
	protected $_sqlExpr = 'NULL';
	protected $_name = false;
	protected $_sqlExprPrint = false;
	protected $_renderer = null;

	public function __construct($sqlExpr = 'NULL', $name = false, $sqlExprPrint = false)
	{
		$this->_sqlExpr = $sqlExpr;
		$this->_name = $name;
		$this->_sqlExprPrint = $sqlExprPrint;
	}

	public function hasValue()
	{
		return ($this->_sqlExpr !== '' && $this->_sqlExpr !== null && $this->_sqlExpr !== false);
	}

	public function setSql($sql)
	{
		$this->_sqlExpr = $sql;
	}

	public function sql()
	{
		return $this->_sqlExpr;
	}

	public function setPrinted($printed)
	{
		$this->_sqlExprPrint = $printed;
	}

	public function printed()
	{
		return ($this->_sqlExprPrint !== false ? $this->_sqlExprPrint : $this->_sqlExpr);
	}

	public function setName($name)
	{
		$this->_name = $name;
	}

	public function name()
	{
		return $this->_name;
	}

	public function setRenderer(\Closure $renderer = null)
	{
		$this->_renderer = $renderer;
	}

	public function renderer()
	{
		return $this->_renderer;
	}
}