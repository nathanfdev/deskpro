<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Pierre Minnieur <pm@pierre-minnieur.de>
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

class TwitterAccountFriend extends EntityRepository
{
	/**
	 * @param integer $accountId
	 * @param integer $userId
	 * @return null|\Application\DeskPRO\Entity\TwitterAccountFriend
	 */
	public function findOneByAccountIdAndUserId($accountId, $userId)
	{
		$friend = $this->getEntityManager()->createQuery("
			SELECT f
			FROM DeskPRO:TwitterAccountFriend f
			WHERE f.account = :account
			AND f.user = :user
		")->setMaxResults(1)->execute(array(
			'account' => $accountId,
			'user' => $userId
		));

		if (!$friend || count($friend) != 1) {
			return null;
		}

		return $friend[0];
	}
}
