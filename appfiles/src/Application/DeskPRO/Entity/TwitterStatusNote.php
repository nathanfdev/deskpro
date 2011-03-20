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
 * Twitter Status Note
 *
 * @orm:Entity
 * @orm:Table(name="twitter_statuses_notes")
 * @orm:HasLifecycleCallbacks
 */
class TwitterStatusNote extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var integer
	 * @orm:Id
	 * @orm:GeneratedValue(strategy="AUTO")
	 * @orm:Column(name="id", type="bigint")
	 */
	protected $id;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterStatus
	 * @orm:ManyToOne(targetEntity="TwitterStatus", inversedBy="notes")
	 * @orm:JoinColumn(name="status_id", referencedColumnName="id")
	 */
	protected $status;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", inversedBy="twitter_status_notes")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;

	/**
	 * @var string
	 * @orm:Column(name="text", type="string", length=4000)
	 */
	protected $text;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created", type="datetime")
	 */
	protected $date_created;

	public function __construct()
	{
		$this->date_created = new \DateTime('now');
	}

	public function getStatusId()
	{

	}

	public function setStatusId($id)
	{
	}

	public function getPersonId()
	{

	}

	public function setPersonId($id)
	{

	}
}
