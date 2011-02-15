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
		$expire = $expire->format('Y-m-d H:m:s');

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