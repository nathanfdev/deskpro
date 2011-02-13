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
 * Twitter Status
 *
 * @orm:Table(name="twitter_statuses")
 * @orm:HasLifecycleCallbacks
 */
class TwitterStatus extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var integer
	 * @orm:Id
	 * @orm:GeneratedValue(strategy="NONE")
	 * @orm:Column(name="id", type="bigint")
	 */
	protected $id;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterUser
	 * @orm:ManyToOne(targetEntity="TwitterUser")
	 * @orm:JoinColumn(name="user_id", referencedColumnName="id")
	 */
	protected $user;

	/**
	 * @var string
	 * @orm:Column(name="text", type="string", length=4000)
	 */
	protected $text;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterStatus
	 * @orm:ManyToOne(targetEntity="TwitterStatus")
	 * @orm:JoinColumn(name="in_reply_to_status_id", referencedColumnName="id", nullable=true)
	 */
	protected $in_reply_to_status = null;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterUser
	 * @orm:ManyToOne(targetEntity="TwitterUser")
	 * @orm:JoinColumn(name="in_reply_to_user_id", referencedColumnName="id", nullable=true)
	 */
	protected $in_reply_to_user = null;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterUser
	 * @orm:ManyToOne(targetEntity="TwitterUser")
	 * @orm:JoinColumn(name="recipient_id", referencedColumnName="id", nullable=true)
	 */
	protected $recipient = null;

	/**
	 * @var Boolean
	 * @orm:Column(name="truncated", type="boolean")
	 */
	protected $truncated = false;

	/**
	 * @var Boolean
	 * @orm:Column(name="favorited", type="boolean")
	 */
	protected $favorited = false;

	/**
	 * @var Boolean
	 * @orm:Column(name="archived", type="boolean")
	 */
	protected $archived = false;

	/**
	 * @var \DateTime
	 * @orm:Column(name="created_at", type="datetime")
	 */
	protected $created_at;

	/**
	 * @var double
	 * @orm:Column(name="geo_latitude", type="decimal", nullable=true, precision=10, scale=5)
	 */
	protected $geo_latitude = null;

	/**
	 * @var double
	 * @orm:Column(name="geo_longitude", type="decimal", nullable=true, precision=10, scale=5)
	 */
	protected $geo_longitude = null;

	/**
	 * @var string
	 * @orm:Column(name="text", type="string", length=4000, nullable=true)
	 */
	protected $source = null;

	/**
	 * Constructor
	 */
	public function __construct()
	{
	}

	/**
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param integer $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return TwitterUser
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param TwitterUser $user
	 */
	public function setUser(TwitterUser $user)
	{
		$this->user = $user;
	}

	/**
	 * @return integer
	 */
	public function getUserId()
	{
		return null !== $this->user ? $this->user->getId() : null;
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
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}

	/**
	 * @param string $text
	 */
	public function setText($text)
	{
		$this->text = $text;
	}

	/**
	 * @return Boolean
	 */
	public function isMessage()
	{
		return null !== $this->recipient;
	}

	/**
	 * @return Boolean
	 */
	public function isReply()
	{
		return null !== $this->in_reply_to_status || null !== $this->in_reply_to_user;
	}

	/**
	 * @return TwitterStatus
	 */
	public function getInReplyToStatus()
	{
		return $this->in_reply_to_status;
	}

	/**
	 * @param TwitterStatus $status
	 */
	public function setInReplyToStatus(TwitterStatus $status)
	{
		$this->in_reply_to_status = $status;
	}

	/**
	 * @return integer
	 */
	public function getInReplyToStatusId()
	{
		return null !== $this->in_reply_to_status ? $this->in_reply_to_status->getId() : null;
	}

	/**
	 * @param integer $id
	 */
	public function setInReplyToStatusId($id)
	{
		if ($id && $status = App::getOrm()->getRepository('DeskPRO:TwitterStatus')->find($id)) {
			$this->in_reply_to_status = $status;
		} else {
			$this->in_reply_to_status = null;
		}
	}

	/**
	 * @return TwitterUser
	 */
	public function getInReplyToUser()
	{
		return $this->in_reply_to_user;
	}

	/**
	 * @param TwitterUser $user
	 */
	public function setInReplyToUser(TwitterUser $user)
	{
		$this->in_reply_to_user = $user;
	}

	/**
	 * @return integer
	 */
	public function getInReplyToUserId()
	{
		return null !== $this->in_reply_to_user ? $this->in_reply_to_user->getId() : null;
	}

	/**
	 * @param integer $id
	 */
	public function setInReplyToUserId($id)
	{
		if ($id && $user = App::getOrm()->getRepository('DeskPRO:TwitterUser')->find($id)) {
			$this->in_reply_to_user = $user;
		} else {
			$this->in_reply_to_user = null;
		}
	}

	/**
	 * @return TwitterUser
	 */
	public function getRecipient()
	{
		return $this->recipient;
	}

	/**
	 * @param TwitterUser $user
	 */
	public function setRecipient(TwitterUser $user)
	{
		$this->recipient = $user;
	}

	/**
	 * @return integer
	 */
	public function getRecipientId()
	{
		return null !== $this->recipient ? $this->recipient->getId() : null;
	}

	/**
	 * @param integer $id
	 */
	public function setRecipientId($id)
	{
		if ($id && $user = App::getOrm()->getRepository('DeskPRO:TwitterUser')->find($id)) {
			$this->recipient = $user;
		} else {
			$this->recipient = null;
		}
	}

	/**
	 * @return Boolean
	 */
	public function getTruncated()
	{
		return $this->truncated;
	}

	/**
	 * @return Boolean
	 */
	public function isTruncated()
	{
		return (Boolean) $this->truncated;
	}

	/**
	 * @param Boolean $truncated
	 */
	public function setTruncated(Boolean $truncated)
	{
		$this->truncated = $truncated;
	}

	/**
	 * @return Boolean
	 */
	public function getFavorited()
	{
		return $this->favorited;
	}

	/**
	 * @return Boolean
	 */
	public function isFavorited()
	{
		return (Boolean) $this->favorited;
	}

	/**
	 * @param Boolean $favorited
	 */
	public function setFavorited(Boolean $favorited)
	{
		$this->favorited = $favorited;
	}

	/**
	 * @return Boolean
	 */
	public function getArchived()
	{
		return $this->archived;
	}

	/**
	 * @return Boolean
	 */
	public function isArchived()
	{
		return (Boolean) $this->archived;
	}

	/**
	 * @param Boolean $archived
	 */
	public function setArchived(Boolean $archived)
	{
		$this->archived = $archived;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreatedAt()
	{
		return $this->created_at;
	}

	/**
	 * @param \DateTime $date
	 */
	public function setCreatedAt(\DateTime $date)
	{
		$this->created_at = $date;
	}

	/**
	 * @return double
	 */
	public function getGeoLatitude()
	{
		return $this->geo_latitude;
	}

	/**
	 * @param double $latitude
	 */
	public function setGeoLatitude($latitude)
	{
		$this->geo_latitude = $latitude;
	}

	/**
	 * @return double
	 */
	public function getGeoLongitude()
	{
		return $this->geo_longitude;
	}

	/**
	 * @param double $longitude
	 */
	public function setGeoLongitude($longitude)
	{
		$this->geo_longitude = $longitude;
	}

	/**
	 * @return string
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * @param string $source
	 */
	public function setSource($source)
	{
		$this->source = $source;
	}
}
