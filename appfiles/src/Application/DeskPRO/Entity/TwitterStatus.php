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
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\TwitterStatus")
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
	 * @orm:ManyToOne(targetEntity="TwitterUser", inversedBy="statuses")
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
	 * @orm:ManyToOne(targetEntity="TwitterStatus", inversedBy="replies")
	 * @orm:JoinColumn(name="in_reply_to_status_id", referencedColumnName="id", nullable=true)
	 */
	protected $in_reply_to_status;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TwitterStatus", mappedBy="in_reply_to_status")
	 */
	protected $replies;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterUser
	 * @orm:ManyToOne(targetEntity="TwitterUser", inversedBy="replies")
	 * @orm:JoinColumn(name="in_reply_to_user_id", referencedColumnName="id", nullable=true)
	 */
	protected $in_reply_to_user;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterUser
	 * @orm:ManyToOne(targetEntity="TwitterUser", inversedBy="messages")
	 * @orm:JoinColumn(name="recipient_id", referencedColumnName="id", nullable=true)
	 */
	protected $recipient;

	/**
	 * @var Boolean
	 * @orm:Column(name="is_truncated", type="boolean")
	 */
	protected $is_truncated = false;

	/**
	 * @var Boolean
	 * @orm:Column(name="is_favorited", type="boolean")
	 */
	protected $is_favorited = false;

	/**
	 * @var Boolean
	 * @orm:Column(name="is_archived", type="boolean")
	 */
	protected $is_archived = false;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created", type="datetime")
	 */
	protected $date_created;

	/**
	 * @var double
	 * @orm:Column(name="geo_latitude", type="decimal", nullable=true, precision=10, scale=5)
	 */
	protected $geo_latitude;

	/**
	 * @var double
	 * @orm:Column(name="geo_longitude", type="decimal", nullable=true, precision=10, scale=5)
	 */
	protected $geo_longitude;

	/**
	 * @var string
	 * @orm:Column(name="source", type="string", length=4000, nullable=true)
	 */
	protected $source;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterStatusLong
	 * @orm:OneToOne(targetEntity="TwitterStatusLong", mappedBy="status")
	 */
	protected $long;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TwitterStatusMention", mappedBy="status")
	 */
	protected $mentions;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TwitterStatusTag", mappedBy="status")
	 */
	protected $tags;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TwitterStatusUrl", mappedBy="status")
	 */
	protected $urls;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TwitterStatusNote", mappedBy="status")
	 */
	protected $notes;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->replies = new \Doctrine\Common\Collections\ArrayCollection();

		$this->mentions = new \Doctrine\Common\Collections\ArrayCollection();
		$this->tags = new \Doctrine\Common\Collections\ArrayCollection();
		$this->urls = new \Doctrine\Common\Collections\ArrayCollection();

		$this->notes = new \Doctrine\Common\Collections\ArrayCollection();
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
	 * @return Boolean
	 */
	protected function hasLongVersion()
	{
		return null !== $this->long_version;
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
	 * @return integer
	 */
	public function getInReplyToStatusId()
	{
		if (null !== $this->in_reply_to_status) {
			return $this->in_reply_to_status->getId();
		}

		return 0;
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
	 * @return integer
	 */
	public function getInReplyToUserId()
	{
		if (null !== $this->in_reply_to_user) {
			return $this->in_reply_to_user->getId();
		}

		return 0;
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
	 * @return integer
	 */
	public function getRecipientId()
	{
		if (null !== $this->recipient) {
			return $this->recipient->getId();
		}

		return 0;
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
	public function isTruncated()
	{
		return (Boolean) $this->is_truncated;
	}

	/**
	 * @return Boolean
	 */
	public function isFavorited()
	{
		return (Boolean) $this->is_favorited;
	}

	/**
	 * @return Boolean
	 */
	public function isArchived()
	{
		return (Boolean) $this->is_archived;
	}
}
