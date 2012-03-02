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
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

/**
 * This tracks associations between a user and a usersource.
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\PersonUsersourceAssoc")
 * @ORM_Mapping\Table(name="person_usersource_assoc")
 */
class PersonUsersourceAssoc extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id;

	/**
	 * @var Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", inversedBy="emails")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person;

	/**
	 * The usersource that this scraper is attached to
	 *
	 * @var Usersource
	 * @ORM_Mapping\OneToOne(targetEntity="Usersource")
	 * @ORM_Mapping\JoinColumn(name="usersource_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $usersource;

	/**
	 * The remote users unique ID. This should not change, so it's smart if this is a system
	 * ID such as a UserID.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="identity", type="string", length=255)
	 */
	protected $identity;

	/**
	 * The remote users friendly ID. This is what will be displayed in various interfaces.
	 * This should rarely change, like a username. Since we use $identity, we can handle
	 * if this changes.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="identity_friendly", type="string", length=255)
	 */
	protected $identity_friendly;

	/**
	 * Any raw data returned from the user auth adapter, it might contain useful information
	 * such as auth keys (eg: in twitter or facebook).
	 *
	 * @var array
	 * @ORM_Mapping\Column(name="data", type="array")
	 */
	protected $data = array();

	/**
	 * When the record was first created in the system
	 *
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="created_at", type="datetime")
	 */
	protected $date_created;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}
}
