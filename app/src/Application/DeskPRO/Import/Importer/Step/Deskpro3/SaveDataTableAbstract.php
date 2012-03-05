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
 * @subpackage Import
 */

namespace Application\DeskPRO\Import\Importer\Step\Deskpro3;

abstract class SaveDataTableAbstractStep extends AbstractDeskpro3Step
{
	abstract public function getTable();

	protected $does_exist = null;

	public function getTitle()
	{
		return 'Save Data: ' . $this->getTable();
	}

	public function getPerPage()
	{
		return 1000;
	}

	public function getDoesExist()
	{
		return $this->importer->doesOldTableExist($this->getTable());
	}

	public function countPages()
	{
		if (!$this->getDoesExist()) {
			return 1;
		}
		return $this->getOldDb()->fetchColumn("SELECT COUNT(*) FROM " . $this->getTable());
	}

	public function run($page = 1)
	{
		if (!$this->getDoesExist()) {
			return;
		}

		$table = $this->getTable();
		$start = ($page - 1) * $this->getPerPage();
		$limit = $this->getPerPage();

		$recs = $this->getOldDb()->fetchAll("SELECT * FORM $table LIMIT $start, $limit");
		foreach ($recs as $rec) {
			$this->getDb()->insert('import_datastore', array(
				'typename' => "table_{$table}",
				'data' => serialize($rec)
			));
		}
	}
}
