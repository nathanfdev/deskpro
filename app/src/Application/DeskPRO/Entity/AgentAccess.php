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

use Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use Application\DeskPRO\Entity;

/**
 * Stores agent access zones
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="agent_access")
 */
class AgentAccess extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\ManyToOne(targetEntity="Person")
	 * @ORM_Mapping\JoinColumn(name="person_id", onDelete="cascade", referencedColumnName="id")
	 */
	protected $person;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\ManyToMany(targetEntity="Department", cascade={"persist", "remove", "merge"})
     * @ORM_Mapping\JoinTable(name="agent_department_members", joinColumns={@ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="person_id", onDelete="cascade")}, inverseJoinColumns={@ORM_Mapping\JoinColumn(name="department_id", referencedColumnName="id", onDelete="cascade")})
	 */
	protected $departments = null;

	/**
	 * Can use the agent interface?
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="access_agent", type="boolean")
	 */
	protected $access_agent = false;

	/**
	 * Can use the admin interface?
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="access_admin", type="boolean")
	 */
	protected $access_admin = false;

	/**
	 * Can manage billing/license management?
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="access_billing", type="boolean")
	 */
	protected $access_billing = false;

	/**
	 * Can use reports?
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="access_reports", type="boolean")
	 */
	protected $access_reports = false;

	public function __construct()
	{
		$this->departments = new \Doctrine\Common\Collections\ArrayCollection();
	}
}
