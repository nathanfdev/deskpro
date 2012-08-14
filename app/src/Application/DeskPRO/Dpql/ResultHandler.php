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