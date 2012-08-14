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