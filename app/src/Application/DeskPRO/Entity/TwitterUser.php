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
	protected $profile_image_url;

	/**
	 * @var string
	 */
	protected $language;

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
	protected $location;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var Boolean
	 */
	protected $is_geo_enabled = false;

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

	public function getStatuses()
	{
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
		$entity['language']          = $user->lang;
		$entity['description']       = $user->description;
		$entity['is_protected']      = $user->protected;
		$entity['is_verified']       = $user->verified;
		$entity['location']          = $user->location;
		$entity['is_geo_enabled']    = $user->geo_enabled;

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
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'bigint', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true, ));
		$metadata->mapField(array( 'fieldName' => 'name', 'type' => 'string', 'length' => 40, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'name', ));
		$metadata->mapField(array( 'fieldName' => 'screen_name', 'type' => 'string', 'length' => 20, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'screen_name', ));
		$metadata->mapField(array( 'fieldName' => 'profile_image_url', 'type' => 'string', 'length' => 200, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'profile_image_url', ));
		$metadata->mapField(array( 'fieldName' => 'language', 'type' => 'string', 'length' => 3, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'language', ));
		$metadata->mapField(array( 'fieldName' => 'is_protected', 'type' => 'boolean', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'is_protected', ));
		$metadata->mapField(array( 'fieldName' => 'is_verified', 'type' => 'boolean', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'is_verified', ));
		$metadata->mapField(array( 'fieldName' => 'location', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'columnName' => 'location', ));
		$metadata->mapField(array( 'fieldName' => 'description', 'type' => 'string', 'length' => 500, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'columnName' => 'description', ));
		$metadata->mapField(array( 'fieldName' => 'is_geo_enabled', 'type' => 'boolean', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'is_geo_enabled', ));
		$metadata->mapOneToMany(array( 'fieldName' => 'statuses', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterStatus', 'mappedBy' => 'user',  ));
		$metadata->mapOneToMany(array( 'fieldName' => 'replies', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterStatus', 'mappedBy' => 'in_reply_to_user',  ));
		$metadata->mapOneToMany(array( 'fieldName' => 'mentions', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterStatusMention', 'mappedBy' => 'user',  ));
		$metadata->mapOneToMany(array( 'fieldName' => 'messages', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterStatus', 'mappedBy' => 'recipient',  ));
		$metadata->mapOneToMany(array( 'fieldName' => 'friends', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterAccountFriend', 'mappedBy' => 'user',  ));
		$metadata->mapOneToMany(array( 'fieldName' => 'followers', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterAccountFollower', 'mappedBy' => 'user',  ));
		$metadata->mapManyToOne(array( 'fieldName' => 'account', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterAccount', 'mappedBy' => 'user', 'inversedBy' => NULL, 'joinColumns' => array( ),  ));
	}
}
