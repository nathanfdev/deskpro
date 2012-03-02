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
 * @author Pierre Minnieur <pm@pierre-minnieur.de>
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

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
	 * @var \Application\DeskPRO\Entity\Deal
	 * @ORM_Mapping\ManyToOne(targetEntity="Deal", inversedBy="twitter_status_notes")
	 * @ORM_Mapping\JoinColumn(name="deal_id", referencedColumnName="id")
	 */
	protected $deal;

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
