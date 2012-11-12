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

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

use Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use Application\DeskPRO\Entity;

/**
 * A Twitter Account contains twitter username and accesstoken
 *
 */
abstract class TwitterAccount extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var integer
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $oauth_token;

	/**
	 * @var string
	 */
	protected $oauth_token_secret;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterUser
	 */
	protected $user;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $friends;

	/**
	 * @var array
	 */
	protected $_friend_ids;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $followers;

	/**
	 * @var array
	 */
	protected $_follower_ids;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $searches;

    /**
	 * @var \Doctrine\Common\Collections\ArrayCollection
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
		$this->friends = new \Doctrine\Common\Collections\ArrayCollection();
		$this->followers = new \Doctrine\Common\Collections\ArrayCollection();
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
	 * @param Boolean $cache (optional)
	 * @return array
	 */
	public function getFriendIds($cache = true)
	{
		if (true === $cache && is_array($this->_friend_ids)) {
			return $this->_friend_ids;
		}

		$this->_friend_ids = App::getDb()->fetchAllCol("
			SELECT user_id
			FROM twitter_accounts_friends
			WHERE account_id = ?
			ORDER BY id DESC
		", array($this['id']));

		if (!is_array($this->_friend_ids)) {
			$this->_friend_ids = array($this->_friend_ids);
		}

		return array_unique($this->_friend_ids);
	}

	/**
	 * Retrieve a list of Twitter users following this account.
	 *
	 * @param Boolean $cache (optional)
	 * @return array
	 */
	public function getFollowerIds($cache = true)
	{
		if (true === $cache && is_array($this->_follower_ids)) {
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

		return array_unique($this->_follower_ids);
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

	public function getNewFollowers()
	{
		$query = App::getOrm()->createQuery("
			SELECT f
			FROM DeskPRO:TwitterAccountFriend f
			WHERE f.account = :account_id
			ORDER BY f.id DESC
		");

		$followers = $query
			->setMaxResults(5)
			->setParameters(array(
				'account_id' => $this->getId(),
			))
			->execute();

		return $followers;
	}

	public function countNewFollowers()
	{
		$query = App::getOrm()->createQuery("
			SELECT COUNT(f.id)
			FROM DeskPRO:TwitterAccountFriend f
			WHERE f.account = :account_id
			ORDER BY f.id DESC
		");

		return $query
			->setMaxResults(5)
			->setParameters(array(
				'account_id' => $this->getId(),
			))
			->getSingleScalarResult();
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
		$friendIds = $this->getFriendIds();

		// include accounts' user id
		if ($includeAccount) {
			$friendIds[] = $this->getUserId();
		}

		return App::getOrm()->getRepository('DeskPRO:TwitterStatus')
			->findByUserIds($friendIds, $includeArchived, $sortByDate);
	}

	/**
	 * Retrieve a count of the timeline for this account.
	 *
	 * @param Boolean $includeArchived (optional)
	 * @param Boolean $includeAccount (optional)
	 * @return array
	 */
	public function countTimeline($includeArchived = false, $includeAccount = false)
	{
		// get ids of users account is following
		$friendIds = $this->getFriendIds();

		// include accounts' user id
		if ($includeAccount) {
			$friendIds[] = $this->getUserId();
		}

		return App::getOrm()->getRepository('DeskPRO:TwitterStatus')
			->countByUserIds($friendIds, $includeArchived);
	}

	/**
	 * Count the total inbox statuses
     *
	 * @param Boolean $includeArchived (optional)
	 * @return int
	 */
	public function countInboxTotal($includeArchived = false)
	{
		return $this->countMessages()
			   + $this->countReplies()
			   + $this->countMentions()
			   + $this->countRetweets()
			   ;
	}

	/**
	 * Retrieve a list of messages for this account.
	 *
	 * @param Boolean $includeArchived (optional)
	 * @param string $sortByDate (optional)
	 * @param integer $limit (optional)
	 * @param integer $page (optional)
	 * @return array
	 */
	public function getMessages($includeArchived = false, $sortByDate = 'asc', $limit = 25, $page = 1)
	{
		return App::getOrm()->getRepository('DeskPRO:TwitterStatus')
			->findMessagesForUserId($this->getUserId(), $includeArchived, $sortByDate, $limit, $page);
	}

	/**
	 * Count the messages for this account.
	 *
	 * @param Boolean $includeArchived (optional)
	 * @return array
	 */
	public function countMessages($includeArchived = false)
	{
		return App::getOrm()->getRepository('DeskPRO:TwitterStatus')
			->countMessagesForUserId($this->getUserId(), $includeArchived);
	}

	/**
	 * Retrieve a list of replies for this account.
	 *
	 * @param Boolean $includeArchived (optional)
	 * @param string $sortByDate (optional)
	 * @param integer $limit (optional)
	 * @param integer $page (optional)
	 * @return array
	 */
	public function getReplies($includeArchived = false, $sortByDate = 'asc', $limit = 25, $page = 1)
	{
		return App::getOrm()->getRepository('DeskPRO:TwitterStatus')
			->findRepliesForUserId($this->getUserId(), $includeArchived, $sortByDate, $limit, $page);
	}

	/**
	 * Retrieve a list of replies for this account.
	 *
	 * @param Boolean $includeArchived (optional)
	 * @return array
	 */
	public function countReplies($includeArchived = false)
	{
		return App::getOrm()->getRepository('DeskPRO:TwitterStatus')
			->countRepliesForUserId($this->getUserId(), $includeArchived);
	}

	/**
	 * Retrieve a list of mentions for this account.
	 *
	 * @param Boolean $includeArchived (optional)
	 * @param string $sortByDate (optional)
	 * @param integer $limit (optional)
	 * @param integer $page (optional)
	 * @return array
	 */
	public function getMentions($includeArchived = false, $sortByDate = 'asc', $limit = 25, $page = 1)
	{
		return App::getOrm()->getRepository('DeskPRO:TwitterStatus')
			->findMentionsForUserId($this->getUserId(), $includeArchived, $sortByDate, $limit, $page);
	}

	/**
	 * Count the mentions for this account.
	 *
	 * @return array
	 */
	public function countMentions($includeArchived = false)
	{
		return App::getOrm()->getRepository('DeskPRO:TwitterStatus')
			->countMentionsForUserId($this->getUserId(), $includeArchived);
	}

	/**
	 * Retrieve a list of retweets for this account.
	 *
	 * @param Boolean $includeArchived (optional)
	 * @param string $sortByDate (optional)
	 * @param integer $limit (optional)
	 * @param integer $page (optional)
	 * @return array
	 */
	public function getRetweets($includeArchived = false, $sortByDate = 'asc', $limit = 25, $page = 1)
	{
		return App::getOrm()->getRepository('DeskPRO:TwitterStatus')
			->findRetweetsForUserId($this->getUserId(), $includeArchived, $sortByDate, $limit, $page);
	}

	/**
	 * Count the retweets for this account.
	 *
	 * @return array
	 */
	public function countRetweets($includeArchived = false)
	{
		return App::getOrm()->getRepository('DeskPRO:TwitterStatus')
			->countRetweetsForUserId($this->getUserId(), $includeArchived);
	}

	/**
	 * Retrieve a list of sent statuses for this account.
	 *
	 * @param Boolean $includeArchived (optional)
	 * @param string $sortByDate (optional)
	 * @param integer $limit (optional)
	 * @param integer $page (optional)
	 * @return array
	 */
	public function getOutgoing($includeArchived = false, $sortByDate = 'asc', $limit = 25, $page = 1)
	{
		return App::getOrm()->getRepository('DeskPRO:TwitterStatus')
			->findOutgoingByUserId($this->getUserId(), $includeArchived, $sortByDate, $limit, $page);
	}

	/**
	 * Count the outgoings for this account.
	 *
	 * @return array
	 */
	public function countOutgoing($includeArchived = false)
	{
		return App::getOrm()->getRepository('DeskPRO:TwitterStatus')
			->countOutgoingByUserId($this->getUserId(), $includeArchived);
	}

	/**
	 * @return \Zend\OAuth\Token\Access
	 */
	public function getOauthAccessToken()
	{
		$accessToken = new \Zend\OAuth\Token\Access();
		$accessToken->setToken($this['oauth_token']);
		$accessToken->setTokenSecret($this['oauth_token_secret']);

		return $accessToken;
	}

	/**
	 * @return integer
	 */
	public function countStarredStatuses($includeArchived = false)
	{
		$userIds = $this->getFriendIds();
		$userIds[] = $this->getUserId();

		$query = sprintf("
			SELECT COUNT(s.id)
			FROM twitter_statuses s
			WHERE s.user_id IN (%s)
			AND s.is_favorited = 1
		", implode(',', $userIds));

		if (!$includeArchived) {
			$query .= " AND s.is_archived = 0 ";
		}

		return App::getDb()->fetchColumn($query);

	}



	############################################################################
	# Doctrine Metadata
	############################################################################


	public static function x_loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TwitterAccount';
		$metadata->setPrimaryTable(array( 'name' => 'twitter_accounts', ));
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT);
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'bigint', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true, ));
		$metadata->mapField(array( 'fieldName' => 'oauth_token', 'type' => 'string', 'length' => 4000, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'oauth_token', ));
		$metadata->mapField(array( 'fieldName' => 'oauth_token_secret', 'type' => 'string', 'length' => 4000, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'oauth_token_secret', ));
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
		$metadata->mapManyToOne(array( 'fieldName' => 'user', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterUser', 'mappedBy' => NULL, 'inversedBy' => 'account', 'joinColumns' => array( 0 => array( 'name' => 'user_id', 'referencedColumnName' => 'id', 'unique' => true, 'nullable' => true, 'onDelete' => NULL, 'columnDefinition' => NULL, ), ),  ));
		$metadata->mapOneToMany(array( 'fieldName' => 'friends', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterAccountFriend', 'mappedBy' => 'account',  ));
		$metadata->mapOneToMany(array( 'fieldName' => 'followers', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterAccountFollower', 'mappedBy' => 'account',  ));
		$metadata->mapOneToMany(array( 'fieldName' => 'searches', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterAccountSearch', 'mappedBy' => 'account',  ));
		$metadata->mapManyToMany(array( 'fieldName' => 'persons', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'joinTable' => array( 'name' => 'twitter_accounts_person', 'schema' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'account_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => NULL, 'columnDefinition' => NULL, ), ), 'inverseJoinColumns' => array( 0 => array( 'name' => 'person_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => NULL, 'columnDefinition' => NULL, ), ), ), ));
	}
}
