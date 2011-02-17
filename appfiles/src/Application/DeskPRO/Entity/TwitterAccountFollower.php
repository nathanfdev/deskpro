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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

/**
 * Twitter Account Follower
 *
 * Whether Account is following User or Account is followed by User is
 * determined by the `role` field.
 *
 *   ROLE_FOLLOWED:  Account <- User
 *   ROLE_FOLLOWING: Account -> User
 *
 * @orm:Table(name="twitter_accounts_followers")
 * @orm:HasLifecycleCallbacks
 */
class TwitterAccountFollower extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * Account is followed by User.
	 *
	 * @var string
	 */
	const ROLE_FOLLOWED = 'followed';

	/**
	 * Account is following User.
	 *
	 * @var string
	 */
	const ROLE_FOLLOWING = 'following';

	/**
	 * @var \Application\DeskPRO\Entity\TwitterAccount
	 * @orm:ManyToOne(targetEntity="TwitterAccount")
	 * @orm:JoinColumn(name="account_id", referencedColumnName="id")
	 */
	protected $account;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterUser
	 * @orm:ManyToOne(targetEntity="TwitterUser")
	 * @orm:JoinColumn(name="user_id", referencedColumnName="id")
	 */
	protected $user;

	/**
	 * @var string
	 * @orm:Column(name="role", type="string")
	 */
	protected $role = self::ROLE_FOLLOWED;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->role = self::ROLE_FOLLOWED;
	}

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

	/**
	 * Whether Account is followed by User.
	 *
	 * @return Boolean
	 */
	public function isFollowed()
	{
		return self::ROLE_FOLLOWED == $this->role;
	}

	/**
	 * Whether Account is following User.
	 *
	 * @return Boolean
	 */
	public function isFollowing()
	{
		return self::ROLE_FOLLOWING == $this->role;
	}

	/**
	 * @param string $role
	 * @throws \InvalidArgumentException
	 */
	public function setRole($role)
	{
		if (!in_array($role, array(self::ROLE_FOLLOWED, self::ROLE_FOLLOWING))) {
			throw new \InvalidArgumentException(sprintf('Invalid role "%s" specified.', $role));
		}

		$this->role = $role;
	}
}