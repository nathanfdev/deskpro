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
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use \Doctrine\ORM\EntityRepository;

class ClientChannelSubscription extends AbstractEntityRepository
{
	public function getSubscriptionsForClient($session_id)
	{
		if ($session_id instanceof Entity\Session) {
			$session_id = $session_id['id'];
		}

		$expire = new \DateTime('-10 minutes');
		$expire = $expire->format('Y-m-d H:i:s');

		$subs = $this->getEntityManager()->createQuery("
			SELECT s
			FROM DeskPRO:ClientChannelSubscription s
			WHERE s.session = ?1
			AND s.date_ping > ?2
		")->execute(array(1=>$session_id, 2=>$expire));

		return $subs;
	}

	public function findSubscriptionForClient($channel, $session_id)
	{
		if ($session_id instanceof Entity\Session) {
			$session_id = $session_id['id'];
		}

		$subs = $this->getEntityManager()->createQuery("
			SELECT s
			FROM DeskPRO:ClientChannelSubscription s
			WHERE s.session = ?1 AND s.channel = ?2
			ORDER BY s.id DESC
		")->setMaxResults(1)->execute(array(1=>$session_id, 2=>$channel));

		if (!$subs OR !count($subs)) {
			return null;
		}

		return $subs[0];
	}
}
