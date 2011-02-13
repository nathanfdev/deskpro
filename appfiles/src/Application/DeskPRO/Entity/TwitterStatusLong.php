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
 * Long Reply/Message w/ URL Shortener.
 *
 * @orm:Table(name="twitter_statuses_long")
 * @orm:HasLifecycleCallbacks
 */
class TwitterStatusLong extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var \Application\DeskPRO\Entity\TwitterStatus
	 * @orm:ManyToOne(targetEntity="TwitterStatus")
	 * @orm:JoinColumn(name="status_id", referencedColumnName="id", nullable=true)
	 */
	protected $status;

	/**
	 * @var string
	 * @orm:Column(name="text", type="string", length="4000")
	 */
	protected $text;

	/**
	 * @var Boolean
	 * @orm:Column(name="public", type="boolean")
	 */
	protected $public = false;

	/**
	 * @var \DateTime
	 * @orm:Column(name="created_at", type="datetime")
	 */
	protected $created_at;

	/**
	 * @var Boolean
	 * @orm:Column(name="read", type="boolean")
	 */
	protected $read = false;

	/**
	 * @var \DateTime
	 * @orm:Column(name="read_at", type="datetime", nullable=true)
	 */
	protected $read_at = null;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->created_at = new \DateTime();
	}

	/**
	 * @return TwitterStatus
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @param TwitterStatus $status
	 */
	public function setStatus(TwitterStatus $status)
	{
		$this->status = $status;
	}

	/**
	 * @return integer
	 */
	public function getStatusId()
	{
		return null !== $this->status ? $this->status->getId() : null;
	}

	/**
	 * @param integer $id
	 */
	public function setStatusId($id)
	{
		$this->status = null;

		if ($id && $status = App::getOrm()->getRepository('DeskPRO:TwitterStatus')->find($id)) {
			$this->status = $status;
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
	public function getPublic()
	{
		return $this->public;
	}

	/**
	 * @return Boolean
	 */
	public function isPublic()
	{
		return (Boolean) $this->public;
	}

	/**
	 * @param Boolean $public
	 */
	public function setPublic(Boolean $public)
	{
		$this->public = $public;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreatedAt()
	{
		return $this->create_at;
	}

	/**
	 * @param \DateTime $date
	 */
	public function setCreatedAt(\DateTime $date)
	{
		$this->created_at = $date;
	}

	/**
	 * @return Boolean
	 */
	public function getRead()
	{
		return $this->read;
	}

	/**
	 * @return Boolean
	 */
	public function isRead()
	{
		return (Boolean) $this->read;
	}

	/**
	 * @param Boolean $read
	 */
	public function setRead(Boolean $read)
	{
		$this->read = $read;
	}
}
