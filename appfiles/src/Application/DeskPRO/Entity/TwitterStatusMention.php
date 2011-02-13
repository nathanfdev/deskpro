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
 * Twitter Status Mention
 *
 * @orm:Table(name="twitter_statuses_mentions")
 * @orm:HasLifecycleCallbacks
 */
class TwitterStatusMention extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var \Application\DeskPRO\Entity\TwitterStatus
	 * @orm:ManyToOne(targetEntity="TwitterStatus")
	 * @orm:JoinColumn(name="status_id", referencedColumnName="id", nullable=true)
	 */
	protected $status;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterUser
	 * @orm:ManyToOne(targetEntity="TwitterUser")
	 * @orm:JoinColumn(name="user_id", referencedColumnName="id")
	 */
	protected $user;

	/**
	 * @var integer
	 * @orm:Column(name="starts", type="integer")
	 */
	protected $starts = 0;

	/**
	 * @var integer
	 * @orm:Column(name="ends", type="integer")
	 */
	protected $ends = 0;

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
	 * @return integer
	 */
	public function getStarts()
	{
		return $this->starts;
	}

	/**
	 * @param integer $starts
	 */
	public function setStarts($starts)
	{
		$this->starts = $starts;
	}

	/**
	 * @return integer
	 */
	public function getEnds()
	{
		return $this->ends;
	}

	/**
	 * @param integer $ends
	 */
	public function setEnds($ends)
	{
		$this->ends = $ends;
	}
}