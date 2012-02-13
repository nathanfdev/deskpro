<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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
