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
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\ServerMysqlStatus;

use Application\DeskPRO\App;
use Doctrine\ORM\EntityManager;

class ServerMysqlStatus
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */

	protected $em;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	/**
	 * @return array
	 */

	public function getMysqlStatus()
	{
		return $this->_getInfo();
	}

	/**
	 * @return array
	 */

	protected function _getInfo()
	{
		try {

			$mysql_processes = App::getDb()->fetchAll("SHOW PROCESSLIST");

		} catch(\Exception $e) {

			$mysql_processes = null;
		}

		try {

			$mysql_status = App::getDb()->fetchAllKeyValue("SHOW STATUS", array(), array(), 0, 1);

		} catch(\Exception $e) {

			$mysql_status = null;
		}

		return array(
			'mysql_processes' => $mysql_processes,
			'mysql_status'    => $mysql_status
		);

	}
}