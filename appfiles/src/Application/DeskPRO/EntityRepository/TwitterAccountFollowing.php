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

use \Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

class TwitterAccountFollowing extends EntityRepository
{
	/**
	 * @param integer $accountId
	 * @param integer $userId
	 * @return null|\Application\DeskPRO\Entity\TwitterAccountFollowing
	 */
	public function findOneByAccountIdAndUserId($accountId, $userId)
	{
		$following = $this->getEntityManager()->createQuery("
			SELECT f
			FROM   DeskPRO:TwitterAccountFollowing f
			WHERE  f.account = :account AND f.user = :user
		")->setMaxResults(1)->execute(array(
			'account' => $accountId,
			'user'    => $userId
		));

		if (!$following || count($following) != 1) {
			return null;
		}

		return $following;
	}
}
