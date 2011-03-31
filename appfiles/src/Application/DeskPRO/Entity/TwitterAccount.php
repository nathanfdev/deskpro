<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Basil Thoppil <basil.thoppil@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use \Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use \Application\DeskPRO\Entity;

/**
 * A Twitter Account contains twitter username and accesstoken
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\TwitterAccount")
 * @orm:Table(name="twitter_accounts")
 */
class TwitterAccount extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var integer
	 * @orm:Id
	 * @orm:GeneratedValue(strategy="IDENTITY")
	 * @orm:Column(name="id", type="bigint")
	 */
	protected $id;

	/**
	 * @var string
	 * @orm:Column(name="oauth_token", type="string", length=4000)
	 */
	protected $oauth_token;

	/**
	 * @var string
	 * @orm:Column(name="oauth_token_secret", type="string", length=4000)
	 */
	protected $oauth_token_secret;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterUser
	 * @orm:OneToOne(targetEntity="TwitterUser", inversedBy="account")
	 * @orm:JoinColumn(name="user_id", referencedColumnName="id")
	 */
	protected $user;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TwitterAccountFollowing", mappedBy="account")
	 */
	protected $following;

	/**
	 * @var array
	 */
	protected $_following_ids;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TwitterAccountFollower", mappedBy="account")
	 */
	protected $followers;

	/**
	 * @var array
	 */
	protected $_follower_ids;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TwitterAccountSearch", mappedBy="account")
	 */
	protected $searches;

    /**
	 * @var \Doctrine\Common\Collections\ArrayCollection
     * @orm:ManyToMany(targetEntity="Person", inversedBy="twitter_accounts")
     * @orm:JoinTable(name="twitter_accounts_person",
     *   joinColumns={@orm:JoinColumn(name="account_id", referencedColumnName="id")},
     *   inverseJoinColumns={@orm:JoinColumn(name="person_id", referencedColumnName="id")}
     * )
     */
	protected $persons;

	/**
	 * @var array
	 */
	protected $_person_ids;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->followers = new \Doctrine\Common\Collections\ArrayCollection();
		$this->following = new \Doctrine\Common\Collections\ArrayCollection();
		$this->searches = new \Doctrine\Common\Collections\ArrayCollection();
		$this->persons = new \Doctrine\Common\Collections\ArrayCollection();
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
	 * Retrieve a list of Twitter users this account follows.
	 *
	 * @return array
	 */
	public function getFollowingIds()
	{
		if (is_array($this->_following_ids)) {
			return $this->_following_ids;
		}

		$this->_following_ids = App::getDb()->fetchAllCol("
			SELECT user_id
			FROM twitter_accounts_following
			WHERE account_id = ?
			ORDER BY id DESC
		", array($this['id']));

		if (!is_array($this->_following_ids)) {
			$this->_following_ids = array($this->_following_ids);
		}

		return $this->_following_ids;
	}

	/**
	 * Retrieve a list of Twitter users following this account.
	 *
	 * @return array
	 */
	public function getFollowerIds()
	{
		if (is_array($this->_follower_ids)) {
			return $this->_follower_ids;
		}
		$this->_follower_ids = App::getDb()->fetchAllCol("
			SELECT user_id
			FROM twitter_accounts_followers
			WHERE account_id = ?
			ORDER BY id DESC
		", array($this['id']));

		if (!is_array($this->_follower_ids)) {
			$this->_follower_ids = array($this->_follower_ids);
		}

		return $this->_follower_ids;
	}

	/**
	 * Retrieve a list of associated Person ids.
	 *
	 * @return array
	 */
	public function getPersonIds()
	{
		if (is_array($this->_person_ids)) {
			return $this->_person_ids;
		}

		$this->_person_ids = App::getDb()->fetchAllCol("
			SELECT person_id
			FROM twitter_accounts_person
			WHERE account_id = ?
		", array($this['id']));

		if (!is_array($this->_person_ids)) {
			$this->_person_ids = array($this->_person_ids);
		}

		return $this->_person_ids;
	}

	/**
	 * Retrieve a timeline for this account.
	 *
	 * @param Boolean $includeArchived (optional)
	 * @param Boolean $includeAccount (optional)
	 * @param string $sortByDate (optional)
	 * @return array
	 */
	public function getTimeline($includeArchived = false, $includeAccount = false, $sortByDate = 'asc')
	{
		// get ids of users account is following
		$followingIds = $this->getFollowingIds();

		// include accounts' user id
		if ($includeAccount) {
			$followingIds[] = $this->getUserId();
		}

		return App::getOrm()->getRepository('DeskPRO:TwitterStatus')
			->findByUserIds($followingIds, $includeArchived, $sortByDate);
	}

	/**
	 * Retrieve a list of messages for this account.
	 *
	 * @param string $sortByDate (optional)
	 * @return array
	 */
	public function getMessages($sortByDate = 'asc')
	{
		return App::getOrm()->getRepository('DeskPRO:TwitterStatus')
			->findMessagesForUserId($this->getUserId(), $sortByDate);
	}

	/**
	 * Retrieve a list of replies for this account.
	 *
	 * @param string $sortByDate (optional)
	 * @return array
	 */
	public function getReplies($sortByDate = 'asc')
	{
		return App::getOrm()->getRepository('DeskPRO:TwitterStatus')
			->findRepliesForUserId($this->getUserId(), $sortByDate);
	}

	/**
	 * Retrieve a list of mentions for this account.
	 *
	 * @param string $sortByDate (optional)
	 * @return array
	 */
	public function getMentions($sortByDate = 'asc')
	{
		return App::getOrm()->getRepository('DeskPRO:TwitterStatus')
			->findMentionsForUserId($this->getUserId(), $sortByDate);
	}

	/**
	 * Retrieve a list of retweets for this account.
	 *
	 * @param string $sortByDate (optional)
	 * @return array
	 */
	public function getRetweets($sortByDate = 'asc')
	{
		return App::getOrm()->getRepository('DeskPRO:TwitterStatus')
			->findRetweetsForUserId($this->getUserId(), $sortByDate);
	}

	/**
	 * Retrieve a list of sent statuses for this account.
	 *
	 * @param string $sortByDate (optional)
	 * @return array
	 */
	public function getOutgoing($sortByDate = 'asc')
	{
		return App::getOrm()->getRepository('DeskPRO:TwitterStatus')
			->findOutgoingByUserId($this->getUserId(), $sortByDate);
	}

	/**
	 * @return \Zend_Oauth_Token_Access
	 */
	public function getOauthAccessToken()
	{
		$accessToken = new \Zend_Oauth_Token_Access();
		$accessToken->setToken($this['oauth_token']);
		$accessToken->setTokenSecret($this['oauth_token_secret']);

		return $accessToken;
	}

	/**
	 * @return integer
	 */
	public function countStarredStatuses()
	{
		$userIds = $this->getFollowingIds();
		$userIds[] = $this->getUserId();

		return App::getDb()->fetchColumn(sprintf("
			SELECT COUNT(s.id)
			FROM twitter_statuses s
			WHERE s.user_id IN (%s)
			AND s.is_favorited = 1
		", implode(',', $userIds)));
	}

	/**
	 * @return integer
	 * @todo implement
	 */
	public function countAssignedStatusesToAgent()
	{
		return 0;
	}

	/**
	 * @return integer
	 * @todo implement
	 */
	public function countAssignedStatusesToTeam()
	{
		return 0;
	}
}