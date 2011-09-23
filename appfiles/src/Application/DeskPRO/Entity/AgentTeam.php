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
 * An agent team is a group of agents. Similar to usergroups but for agents.
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\AgentTeam")
 * @ORM_Mapping\Table(name="agent_teams")
 * @ORM_Mapping\HasLifecycleCallbacks
 */
class AgentTeam extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY")
	 * @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;


	/**
	 * @var string
	 * @ORM_Mapping\Column(name="name", type="string", length=255)
	 */
	protected $name;


	/**
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\ManyToMany(targetEntity="Person", cascade={"persist", "remove", "merge"})
     * @ORM_Mapping\JoinTable(name="agent_team_members", joinColumns={@ORM_Mapping\JoinColumn(name="team_id", referencedColumnName="id", onDelete="cascade")}, inverseJoinColumns={@ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")})
	 * @ORM_Mapping\OrderBy({"first_name" = "ASC", "last_name" = "ASC"})
	 */
	protected $members = null;

  /**
	 * The tasks assigned to this team.
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="Task", mappedBy="assigned_agent_team", cascade={"persist", "remove", "merge"})
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
