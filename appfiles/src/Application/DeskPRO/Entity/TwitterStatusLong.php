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
	 * @orm:Column(name="is_public", type="boolean")
	 */
	protected $is_public = false;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created", type="datetime")
	 */
	protected $date_created;

	/**
	 * @var Boolean
	 * @orm:Column(name="is_read", type="boolean")
	 */
	protected $is_read = false;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_read", type="datetime", nullable=true)
	 */
	protected $date_read = null;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->date_created = new \DateTime();
	}

	/**
	 * @return integer
	 */
	public function getStatusId()
	{
		if (null !== $this->status) {
			return $this->status->getId();
		}

		return 0;
	}

	/**
	 * @param integer $id
	 */
	public function setStatusId($id)
	{
		if ($id && $status = App::getOrm()->getRepository('DeskPRO:TwitterStatus')->find($id)) {
			$this->status = $status;
		} else {
			$this->status = null;
		}
	}

	/**
	 * @return Boolean
	 */
	public function isPublic()
	{
		return (Boolean) $this->is_public;
	}

	/**
	 * @return Boolean
	 */
	public function isRead()
	{
		return (Boolean) $this->is_read;
	}
}
