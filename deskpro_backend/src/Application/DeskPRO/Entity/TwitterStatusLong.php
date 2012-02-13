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

use Doctrine\ORM\Mapping as ORM_Mapping;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

/**
 * Twitter Status
 *
 * Long Reply/Message w/ URL Shortener.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="twitter_statuses_long")
 * @ORM_Mapping\HasLifecycleCallbacks
 */
class TwitterStatusLong extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var integer
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\GeneratedValue(strategy="AUTO")
	 * @ORM_Mapping\Column(name="id", type="bigint")
	 */
	protected $id;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterStatus
	 * @ORM_Mapping\OneToOne(targetEntity="TwitterStatus")
	 * @ORM_Mapping\JoinColumn(name="status_id", referencedColumnName="id")
	 */
	protected $status;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="text", type="string", length=4000)
	 */
	protected $text;

	/**
	 * @var Boolean
	 * @ORM_Mapping\Column(name="is_public", type="boolean")
	 */
	protected $is_public = false;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created", type="datetime")
	 */
	protected $date_created;

	/**
	 * @var Boolean
	 * @ORM_Mapping\Column(name="is_read", type="boolean")
	 */
	protected $is_read = false;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_read", type="datetime", nullable=true)
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
