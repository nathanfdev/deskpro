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
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person as PersonEntity;

class RateLimitLog extends AbstractEntityRepository
{
	public function save($action, PersonEntity $person, $ip = null)
	{
		$ip = $ip ? ip2long($ip) : 0;
		$this->getEntityManager()->getConnection()->executeQuery(sprintf(
			'insert into %s (action, ip, person_id, date_created) values (:action, %d, %d, NOW())',
			$this->getTableName(), $ip, $person['id']
		), array('action' => $action));
	}

	public function count($action, $time, PersonEntity $person, $ip = null)
	{
		$q = sprintf(
			'select count(*) from %s where action = :action and date_created >= :date and (ip = %d or person_id = %d)',
			$this->getTableName(), $ip ? ip2long($ip) : 0, $person['id']
		);

		$params = array(
			'action' => $action,
			'date' => date('Y-m-d H:i:s', time() - (int) $time),
		);

		return (int) $this->getEntityManager()->getConnection()->executeQuery($q, $params)->fetchColumn();
	}
}
