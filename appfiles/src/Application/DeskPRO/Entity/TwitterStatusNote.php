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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

/**
 * Twitter Status Note
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="twitter_statuses_notes")
 * @ORM_Mapping\HasLifecycleCallbacks
 */
class TwitterStatusNote extends \Application\DeskPRO\Domain\DomainObject
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
	 * @ORM_Mapping\ManyToOne(targetEntity="TwitterStatus", inversedBy="notes")
	 * @ORM_Mapping\JoinColumn(name="status_id", referencedColumnName="id")
	 */
	protected $status;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", inversedBy="twitter_status_notes")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="text", type="string", length=4000)
	 */
	protected $text;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created", type="datetime")
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
