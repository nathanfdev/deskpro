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
 * An agent team is a group of agents. Similar to usergroups but for agents.
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\AgentTeam")
 * @orm:Table(name="agent_teams")
 */
class AgentTeam extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY")
	 * @orm:Column(name="id", type="integer")
	 */
	protected $id = null;


	/**
	 * @var string
	 * @orm:Column(name="name", type="string", length=255)
	 */
	protected $name;


	/**
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @orm:ManyToMany(targetEntity="Person", cascade={"persist", "remove", "merge"})
     * @orm:JoinTable(name="agent_team_members", joinColumns={@orm:JoinColumn(name="team_id", referencedColumnName="id")}, inverseJoinColumns={@orm:JoinColumn(name="person_id", referencedColumnName="id")})
	 * @orm:OrderBy({"first_name" = "ASC", "last_name" = "ASC"})
	 */
	protected $members = null;

  /**
	 * The tasks assigned to this team.
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="Task", mappedBy="assigned_agent_team", cascade={"persist", "remove", "merge"})
	 */
	protected $assigned_tasks;



	/**
	 * Creates a new team.
	 */
	public function __construct()
	{
		$this->assigned_tasks = new \Doctrine\Common\Collections\ArrayCollection();
	}



	public function addPerson(Entity\Person $person)
	{
		$this->members->add($person);
	}

	public function removePerson(Entity\Person $person)
	{
		$this->members->removeElement($person);
	}
}