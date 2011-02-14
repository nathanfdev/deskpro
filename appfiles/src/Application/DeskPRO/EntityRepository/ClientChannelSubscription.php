<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EntityRepository;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Doctrine\ORM\EntityRepository;

class ClientChannelSubscription extends EntityRepository
{
	public function getSubscriptionsForClient($session_id)
	{
		if ($session_id instanceof Entity\Session) {
			$session_id = $session_id['id'];
		}

		$expire = new \DateTime('-10 minutes');

		$subs = $this->getEntityManager()->createQuery("
			SELECT s
			FROM DeskPRO:ClientChannelSubscription s
			WHERE session_id = ?1
			AND date_ping > ?2
		")->execute(array($session_id, $expire));

		return $subs;
	}

	public function findSubscriptionForClient($channel, $session_id)
	{
		if ($session_id instanceof Entity\Session) {
			$session_id = $session_id['id'];
		}

		$sub = $this->getEntityManager()->createQuery("
			SELECT s
			FROM DeskPRO:ClientChannelSubscription s
			WHERE session_id = ?1 AND channel = ?2
			LIMIT 1
		")->execute(array($session_id, $channel))->first();

		if (!$sub) {
			return null;
		}

		return $sub;
	}
}