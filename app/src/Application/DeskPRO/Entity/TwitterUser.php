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
use Application\DeskPRO\Entity;

/**
 * Twitter User
 *
 */
class TwitterUser extends \Application\DeskPRO\Domain\DomainObject
{
	const TIMELINE_UPDATE_FREQUENCY = 900;
	const PROFILE_UPDATE_FREQUENCY = 86400;

	/**
	 * @var integer
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $screen_name;

	/**
	 * @var string
	 */
	protected $profile_image_url = '';

	/**
	 * @var string
	 */
	protected $language = '';

	/**
	 * @var Boolean
	 */
	protected $is_protected = false;

	/**
	 * @var Boolean
	 */
	protected $is_verified = false;

	/**
	 * @var string
	 */
	protected $location = '';

	/**
	 * @var string
	 */
	protected $description = '';

	/**
	 * @var string
	 */
	protected $url = '';

	/**
	 * @var Boolean
	 */
	protected $is_geo_enabled = false;

	/**
	 * @var bool
	 */
	protected $is_stub = false;

	/**
	 * @var \DateTime|null
	 */
	protected $last_timeline_update;

	/**
	 * @var \DateTime|null
	 */
	protected $last_profile_update;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $statuses;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $replies;

	// protected $retweets;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $mentions;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $messages;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $friends;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $followers;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterAccount
	 */
	protected $account;

	protected static $_stubs = array();
	protected static $_processing_stubs = false;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->statuses = new \Doctrine\Common\Collections\ArrayCollection();
		$this->replies  = new \Doctrine\Common\Collections\ArrayCollection();
		$this->mentions = new \Doctrine\Common\Collections\ArrayCollection();
		$this->messages = new \Doctrine\Common\Collections\ArrayCollection();

		$this->friends = new \Doctrine\Common\Collections\ArrayCollection();
		$this->followers = new \Doctrine\Common\Collections\ArrayCollection();
	}

	/**
	 * @return Boolean
	 */
	public function isProtected()
	{
		return (Boolean) $this->is_protected;
	}

	/**
	 * @return Boolean
	 */
	public function isVerified()
	{
		return (Boolean) $this->is_verified;
	}

	/**
	 * @return Boolean
	 */
	public function isGeoEnabled()
	{
		return (Boolean) $this->is_geo_enabled;
	}

	public function getProfileImageUrl($size = 'normal')
	{
		if ($size == 'normal') {
			return $this->profile_image_url;
		} else {
			return str_replace('_normal.', ($size ? "_$size" : '') . '.', $this->profile_image_url);
		}
	}

	public function getStatuses()
	{
		if (!$this->last_timeline_update || $this->last_timeline_update->getTimestamp() < time() - self::TIMELINE_UPDATE_FREQUENCY) {
			$em = App::getOrm();

			$account = $em->getRepository('DeskPRO:TwitterAccount')->getFirst();
			if ($account) {
				// need to grab the first api we can get
				$api = $account->getTwitterApi();
				try {
					$response = $api->get_statusesUser_timeline(array(
						'user_id' => $this->id,
						'count' => 25
					));
					$twitter_service = new \Application\DeskPRO\Service\Twitter();
					foreach ($response AS $status) {
						$twitter_service->processStatus($api, $status);
					}
				} catch (\EpiTwitterException $e) {
				} catch (\EpiOAuthException $e) {
				}

				$this['last_timeline_update'] = new \DateTime();
				$em->persist($this);
				$em->flush();
			}
		}

		return App::getOrm()->getRepository('DeskPRO:TwitterStatus')->findOutgoingForUserId($this->id, true, 'desc');
	}

	public function getMessages()
	{
		return App::getOrm()->getRepository('DeskPRO:TwitterStatus')->findMessagesForUserId($this->id, true, 'desc');
	}

	public function getMentions()
	{
		return App::getOrm()->getRepository('DeskPRO:TwitterStatus')->findMentionsForUserId($this->id, true, 'desc');
	}

	public function offsetGet($offset)
	{
		if (self::$_processing_stubs) {
			return parent::offsetGet($offset);
		}

		if ($this->is_stub && ($offset == 'id' || $offset == 'is_stub')) {
			return parent::offsetGet($offset);
		}

		if ($this->is_stub && self::$_stubs) {
			self::$_processing_stubs = true;
			$em = App::getOrm();
			$account = $em->getRepository('DeskPRO:TwitterAccount')->getFirst();
			if ($account) {
				// need to grab the first api we can get
				$api = $account->getTwitterApi();

				$id_sets = array_chunk(array_keys(self::$_stubs), 100);
				foreach ($id_sets AS $ids) {
					try {
						$response = $api->post_usersLookup(array(
							'user_id' => implode(',', $ids)
						));
						foreach ($response AS $user) {
							if (isset(self::$_stubs[$user->id_str])) {
								$entity = self::$_stubs[$user->id_str];
								$entity->updateFromJson($user);
								$em->persist($entity);
							}
						}
					} catch (\EpiTwitterException $e) {
						break;
					} catch (\EpiOAuthException $e) {
						break;
					}
					// catches prevent any twitter errors from breaking the page
				}

				$em->flush();
			}

			self::$_stubs = array();
			self::$_processing_stubs = false;
		}

		return parent::offsetGet($offset);
	}

	public function updateProfile()
	{
		$account = App::getOrm()->getRepository('DeskPRO:TwitterAccount')->getFirst();
		if ($account) {
			try {
				$response = $account->getTwitterApi()->get_usersShow(array('user_id' => $this->id));
				$this->updateFromJson($response);
			} catch (\EpiTwitterException $e) {
			} catch (\EpiOAuthException $e) {
			}

			$this['last_profile_update'] = new \DateTime();
		}
	}

	public function updateFromJson($user)
	{
		$processing = self::$_processing_stubs;
		self::$_processing_stubs = true; // don't want to trigger loads here

		$this['name']              = $user->name;
		$this['screen_name']       = $user->screen_name;
		$this['profile_image_url'] = $user->profile_image_url;
		$this['url']               = (string)$user->url;
		$this['language']          = (string)$user->lang;
		$this['description']       = (string)$user->description;
		$this['is_verified']       = $user->verified;
		$this['location']          = $user->location;
		$this['is_geo_enabled']    = $user->geo_enabled;
		$this['is_stub']           = false;
		$this['last_profile_update'] = new \DateTime();

		self::$_processing_stubs = $processing;
	}

	public function _checkStub()
	{
		if ($this->is_stub) {
			self::$_stubs[$this->id] = $this;
		}
	}

	/**
	 * @param object $user
	 * @return \Application\DeskPRO\Entity\TwitterUser
	 */
	static public function createFromJson($user)
	{
		$entity                      = new self();
		$entity['id']                = $user->id_str;
		$entity['name']              = $user->name;
		$entity['screen_name']       = $user->screen_name;
		$entity['profile_image_url'] = $user->profile_image_url;
		$entity['url']               = (string)$user->url;
		$entity['language']          = (string)$user->lang;
		$entity['description']       = (string)$user->description;
		$entity['is_protected']      = $user->protected;
		$entity['is_verified']       = $user->verified;
		$entity['location']          = $user->location;
		$entity['is_geo_enabled']    = $user->geo_enabled;
		$entity['is_stub']           = false;
		$entity['last_profile_update'] = new \DateTime();

		return $entity;
	}

	public static function createStub($id)
	{
		$entity = new self();
		$entity['id'] = $id;
		$entity['name'] = '';
		$entity['screen_name'] = '';
		$entity['profile_image_url'] = '';
		$entity['url'] = '';
		$entity['language'] = '';
		$entity['description'] = '';
		$entity['location'] = '';
		$entity['is_stub'] = true;

		return $entity;
	}



	############################################################################
	# Doctrine Metadata
	############################################################################


	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TwitterUser';
		$metadata->setPrimaryTable(array( 'name' => 'twitter_users', ));
		$metadata->addLifecycleCallback('_checkStub', 'postLoad');
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'bigint', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true, ));
		$metadata->mapField(array( 'fieldName' => 'name', 'type' => 'string', 'length' => 40, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'name', ));
		$metadata->mapField(array( 'fieldName' => 'screen_name', 'type' => 'string', 'length' => 20, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'screen_name', ));
		$metadata->mapField(array( 'fieldName' => 'profile_image_url', 'type' => 'string', 'length' => 200, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'profile_image_url', ));
		$metadata->mapField(array( 'fieldName' => 'language', 'type' => 'string', 'length' => 3, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'language', ));
		$metadata->mapField(array( 'fieldName' => 'url', 'type' => 'string', 'length' => 200, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'url', ));
		$metadata->mapField(array( 'fieldName' => 'is_protected', 'type' => 'boolean', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'is_protected', ));
		$metadata->mapField(array( 'fieldName' => 'is_verified', 'type' => 'boolean', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'is_verified', ));
		$metadata->mapField(array( 'fieldName' => 'location', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'columnName' => 'location', ));
		$metadata->mapField(array( 'fieldName' => 'description', 'type' => 'string', 'length' => 500, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'columnName' => 'description', ));
		$metadata->mapField(array( 'fieldName' => 'is_geo_enabled', 'type' => 'boolean', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'is_geo_enabled', ));
		$metadata->mapField(array( 'fieldName' => 'is_stub', 'type' => 'boolean', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'is_stub', ));
		$metadata->mapField(array( 'fieldName' => 'last_timeline_update', 'type' => 'datetime', 'precision' => 0, 'scale' => 0, 'nullable' => true, 'columnName' => 'last_timeline_update', ));
		$metadata->mapField(array( 'fieldName' => 'last_profile_update', 'type' => 'datetime', 'precision' => 0, 'scale' => 0, 'nullable' => true, 'columnName' => 'last_profile_update', ));
		$metadata->mapOneToMany(array( 'fieldName' => 'statuses', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterStatus', 'mappedBy' => 'user',  ));
		$metadata->mapOneToMany(array( 'fieldName' => 'replies', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterStatus', 'mappedBy' => 'in_reply_to_user',  ));
		$metadata->mapOneToMany(array( 'fieldName' => 'mentions', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterStatusMention', 'mappedBy' => 'user',  ));
		$metadata->mapOneToMany(array( 'fieldName' => 'messages', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterStatus', 'mappedBy' => 'recipient',  ));
		$metadata->mapOneToMany(array( 'fieldName' => 'friends', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterAccountFriend', 'mappedBy' => 'user',  ));
		$metadata->mapOneToMany(array( 'fieldName' => 'followers', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterAccountFollower', 'mappedBy' => 'user',  ));
		$metadata->mapManyToOne(array( 'fieldName' => 'account', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterAccount', 'mappedBy' => 'user', 'inversedBy' => NULL, 'joinColumns' => array( ),  ));
	}
}
