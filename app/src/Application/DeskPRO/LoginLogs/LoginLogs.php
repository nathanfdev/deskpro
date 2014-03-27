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
 */

namespace Application\DeskPRO\LoginLogs;

use Application\DeskPRO\App;
use Doctrine\ORM\EntityManager;

class LoginLogs
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */

	protected $em;

	/**
	 * @var int
	 */

	protected $per_page = 50;

	/**
	 * @var int
	 */

	protected $from;

	/**
	 * @param EntityManager $em
	 */

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	/**
	 * @param int $per_page
	 *
	 * @return $this
	 */

	public function setPerPage($per_page)
	{
		$this->per_page = $per_page;

		return $this;
	}

	/**
	 * @param int $page
	 *
	 * @return $this
	 */

	public function setPage($page)
	{
		if ($page == 0) {

			$page = 1;
		}

		$this->from = ($page - 1) * $this->per_page;

		return $this;
	}

    /**
     * @return array
     */

    public function getAll()
    {
		$logs = App::getDb()->fetchAll("
			SELECT login_log.*
			FROM login_log
			LEFT JOIN people ON (people.id = login_log.person_id)
			WHERE people.is_agent
			ORDER BY login_log.id DESC
			LIMIT $this->from, $this->per_page
		");

		foreach ($logs as $key => $log) {

			$logs[$key]['agent_name'] = $this->em->getRepository('DeskPRO:Person')->get($log['person_id'])->display_name;
		}

		return $logs;
    }

	/**
	 * @return int
	 */

	public function getPageCount()
	{
		$count = App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM login_log
			LEFT JOIN people ON (people.id = login_log.person_id)
			WHERE people.is_agent
		");

		return ceil($count / $this->per_page);
	}
}