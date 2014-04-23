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
 * @package Importer
 */

namespace Application\ImportBundle\RecordMapper;

use Doctrine\DBAL\Connection;

class PersonRecordMapper implements RecordMapperInterface
{
	/**
	 * @var \Doctrine\DBAL\Connection
	 */
	private $db;

	/**
	 * @var array
	 */
	private $cache = array();


	/**
	 * @param Connection $db
	 */
	public function __construct(Connection $db)
	{
		$this->db = $db;
	}


	/**
	 * Returns person ID given an email address.
	 *
	 * @param mixed $value
	 * @return int|null
	 */
	public function findIdFromValue($value)
	{
		$value = strtolower($value);

		if (isset($this->cache[$value])) {
			$pid = $this->cache[$value];
		} else {
			$pid = $this->db->fetchColumn("SELECT person_id FROM people_emails WHERE email = ?", array($value)) ?: null;
		}


		unset($this->cache[$value]);
		if ($pid) {
			$this->cache[$value] = $pid;
		}

		// Save up to 5000 cache records
		while (count($this->cache[$value]) > 5000) array_shift($this->cache[$value]);

		return $pid;
	}
}