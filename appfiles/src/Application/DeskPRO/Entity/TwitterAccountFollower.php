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

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

/**
 * Twitter Account followed by a User
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\TwitterAccountFollower")
 * @ORM_Mapping\Table(name="twitter_accounts_followers", uniqueConstraints={
 *     @ORM_Mapping\UniqueConstraint(name="account_user_idx", columns={"account_id", "user_id"})
 * }))
 * @ORM_Mapping\HasLifecycleCallbacks
 */
class TwitterAccountFollower extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var integer
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\GeneratedValue(strategy="AUTO")
	 * @ORM_Mapping\Column(name="id", type="bigint")
	 */
	protected $id;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterAccount
	 * @ORM_Mapping\ManyToOne(targetEntity="TwitterAccount", inversedBy="followers")
	 * @ORM_Mapping\JoinColumn(name="account_id", referencedColumnName="id")
	 */
	protected $account;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterUser
	 * @ORM_Mapping\ManyToOne(targetEntity="TwitterUser", inversedBy="followers")
	 * @ORM_Mapping\JoinColumn(name="user_id", referencedColumnName="id")
	 */
	protected $user;

	/**
	 * @return integer
	 */
	public function getAccountId()
	{
		if (null !== $this->account) {
			return $this->account->getId();
		}

		return 0;
	}

	/**
	 * @param integer $id
	 */
	public function setAccountId($id)
	{
		if ($id && $account = App::getOrm()->getRepository('DeskPRO:TwitterAccount')->find($id)) {
			$this->account = $account;
		} else {
			$this->account = null;
		}
	}

	/**
	 * @return integer
	 */
	public function getUserId()
	{
		if (null !== $this->user) {
			return $this->user->getId();
		}

		return 0;
	}

	/**
	 * @param integer $id
	 */
	public function setUserId($id)
	{
		if ($id && $user = App::getOrm()->getRepository('DeskPRO:TwitterUser')->find($id)) {
			$this->user = $user;
		} else {
			$this->user = null;
		}
	}
}