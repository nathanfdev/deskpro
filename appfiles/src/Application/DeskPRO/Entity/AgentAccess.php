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

use \Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use \Application\DeskPRO\Entity;

/**
 * Stores agent access zones
 *
 * @orm:Entity
 * @orm:Table(name="agent_access")
 */
class AgentAccess extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", onDelete="cascade", referencedColumnName="id")
	 */
	protected $person;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:ManyToMany(targetEntity="Department", cascade={"persist", "remove", "merge"})
     * @orm:JoinTable(name="agent_department_members", joinColumns={@orm:JoinColumn(name="person_id", referencedColumnName="person_id", onDelete="cascade")}, inverseJoinColumns={@orm:JoinColumn(name="department_id", referencedColumnName="id", onDelete="cascade")})
	 */
	protected $departments = null;

	/**
	 * Can use the agent interface?
	 *
	 * @var bool
	 * @orm:Column(name="access_agent", type="boolean")
	 */
	protected $access_agent = false;

	/**
	 * Can use the admin interface?
	 *
	 * @var bool
	 * @orm:Column(name="access_admin", type="boolean")
	 */
	protected $access_admin = false;

	/**
	 * Can manage billing/license management?
	 *
	 * @var bool
	 * @orm:Column(name="access_billing", type="boolean")
	 */
	protected $access_billing = false;

	/**
	 * Can use reports?
	 *
	 * @var bool
	 * @orm:Column(name="access_reports", type="boolean")
	 */
	protected $access_reports = false;

	public function __construct()
	{
		$this->departments = new \Doctrine\Common\Collections\ArrayCollection();
	}
}