<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @package Importer
 */

namespace Application\ImportBundle\RecordMapper;

use Doctrine\DBAL\Connection;
use Orb\Util\Strings;

class CommonRecordMapper implements LearnableRecordMapperInterface
{
	/**
	 * @var \Doctrine\DBAL\Connection
	 */
	protected $db;

	/**
	 * @var string
	 */
	protected $table;

	/**
	 * @var string
	 */
	protected $match_field;

	/**
	 * @var array
	 */
	protected $records;

	/**
	 * @var array
	 */
	protected $record_ids;


	/**
	 * @param Connection $db
	 * @param string $table
	 * @param string $match_field
	 */
	public function __construct(Connection $db, $table, $match_field)
	{
		$this->db = $db;
		$this->table = $table;
		$this->match_field = $match_field;
	}


	/**
	 * Fetches DB records from the databases
	 */
	protected function getDbRecords()
	{
		return $this->db->fetchAll("SELECT * FROM {$this->table} ORDER BY id ASC");
	}


	/**
	 * Inits the lookup records
	 */
	protected function initRecords()
	{
		$this->records = array();
		$this->record_ids = array();

		$raw_records = $this->getDbRecords();
		$id_to_title = array();
		foreach ($raw_records as $rec) {
			$id_to_title[$rec['id']] = $rec[$this->match_field];
		}

		foreach ($raw_records as $rec) {

			$title = $rec[$this->match_field];
			if (isset($rec['parent_id']) && $rec['parent_id'] && isset($id_to_title[$rec['parent_id']])) {
				$title = $id_to_title[$rec['parent_id']] . ' > ' . $title;
			}

			$this->records[$rec['id']] = $this->normalizeMatchField($title);
			$this->record_ids[$rec['id']] = $rec['id'];
		}
	}


	/**
	 * Have the mapper learn a new value. For examlpe, this might add a new value to an internal cache.
	 *
	 * @param mixed $record
	 * @return void
	 */
	public function learnRecord($record)
	{
		if ($this->records === null) {
			$this->initRecords();
		}

		$this->records[$record['id']] = $this->normalizeMatchField($record[$this->match_field]);
		$this->record_ids[$record['id']] = $record['id'];
	}


	/**
	 * @param string $value
	 * @return string
	 */
	protected function normalizeMatchField($value)
	{
		$value = preg_replace('#\s>\s#', '>', $value); // "Parent > Sub" to "Parent>Sub"
		$value = preg_replace('#\s#', '_', $value);    // "With Spaces" to "With_Spaces"
		$value = Strings::utf8_strtolower($value);     // "Example_Cat" to "example_cat"
		return $value;
	}


	/**
	 * Returns person ID given an email address.
	 *
	 * @param mixed $value
	 * @return int|null
	 */
	public function findIdFromValue($value)
	{
		if ($this->records === null) {
			$this->initRecords();
		}

		if (!$this->records) {
			return null;
		}

		if ($value[0] == '@') {
			$id = substr($value, 1);
			return isset($this->record_ids[$id]) ? $this->record_ids[$id] : null;
		}

		$value = $this->normalizeMatchField($value);
		
		return array_search($value, $this->records);
	}
}