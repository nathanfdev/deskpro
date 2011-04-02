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
 * Twitter User
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\TwitterUser")
 * @orm:Table(name="twitter_users")
 * @orm:HasLifecycleCallbacks
 */
class TwitterUser extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var integer
	 * @orm:Id
	 * @orm:GeneratedValue(strategy="NONE")
	 * @orm:Column(name="id", type="bigint")
	 */
	protected $id;

	/**
	 * @var string
	 * @orm:Column(name="name", type="string", length=40)
	 */
	protected $name;

	/**
	 * @var string
	 * @orm:Column(name="screen_name", type="string", length=20)
	 */
	protected $screen_name;

	/**
	 * @var string
	 * @orm:Column(name="profile_image_url", type="string", length=200)
	 */
	protected $profile_image_url;

	/**
	 * @var string
	 * @orm:Column(name="language", type="string", length=3)
	 */
	protected $language;

	/**
	 * @var Boolean
	 * @orm:Column(name="is_protected", type="boolean")
	 */
	protected $is_protected = false;

	/**
	 * @var Boolean
	 * @orm:Column(name="is_verified", type="boolean")
	 */
	protected $is_verified = false;

	/**
	 * @var string
	 * @orm:Column(name="location", type="string", length=255, nullable=true)
	 */
	protected $location;

	/**
	 * @var Boolean
	 * @orm:Column(name="is_geo_enabled", type="boolean")
	 */
	protected $is_geo_enabled = false;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TwitterStatus", mappedBy="user")
	 */
	protected $statuses;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TwitterStatus", mappedBy="in_reply_to_user")
	 */
	protected $replies;

	// protected $retweets;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TwitterStatusMention", mappedBy="user")
	 */
	protected $mentions;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TwitterStatus", mappedBy="recipient")
	 */
	protected $messages;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TwitterAccountFriend", mappedBy="user")
	 */
	protected $friends;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TwitterAccountFollower", mappedBy="user")
	 */
	protected $followers;

	/**
	 * @var Application\DeskPRO\EntityRepository\TwitterAccount
	 * @orm:OneToOne(targetEntity="TwitterAccount", mappedBy="user")
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

	/**
	 * @param \SimpleXMLElement|\Zend_Rest_Client_Result $user
	 * @return \Application\DeskPRO\Entity\TwitterUser
	 */
	static public function createFromXML($user)
	{
		// @TODO check against \SimpleXMLElement & \Zend_Rest_Client_Result

		$entity                      = new self();
		$entity['id']                = (integer) $user->id;
		$entity['name']              = (string) $user->name;
		$entity['screen_name']       = (string) $user->screen_name;
		$entity['profile_image_url'] = (string) $user->profile_image_url;
		$entity['language']          = (string) $user->lang;
		$entity['is_protected']      = (Boolean) (integer) $user->protected;
		$entity['is_verified']       = (Boolean) (integer) $user->verified;
		$entity['location']          = (string) $user->location;
		$entity['is_geo_enabled']    = (Boolean) (integer) $user->geo_enabled;

		return $entity;
	}

	/**
	 * @param \array $user
	 * @return \Application\DeskPRO\Entity\TwitterUser
	 */
	static public function createFromJson(array $user)
	{
		$entity                      = new self();
		$entity['id']                = $user['id_str'];
		$entity['name']              = $user['name'];
		$entity['screen_name']       = $user['screen_name'];
		$entity['profile_image_url'] = $user['profile_image_url'];
		$entity['language']          = $user['lang'];
		$entity['is_protected']      = $user['protected'];
		$entity['is_verified']       = $user['verified'];
		$entity['location']          = $user['location'];
		$entity['is_geo_enabled']    = $user['geo_enabled'];

		return $entity;
	}
}