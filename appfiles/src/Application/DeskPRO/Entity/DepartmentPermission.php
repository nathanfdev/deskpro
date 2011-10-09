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

/**
 * Stores who has access to departments
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\DepartmentPermission")
 * @ORM_Mapping\Table(name="department_permissions")
 */
class DepartmentPermission extends \Application\DeskPRO\Domain\DomainObject
{
	CONST TYPE_PERSON        = 'person';
	CONST TYPE_AGENT_TEAM    = 'agent_team';
	CONST TYPE_USERGROUP     = 'usergroup';

	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id;

	/**
	 * @var \Application\DeskPRO\Entity\Department
	 * @ORM_Mapping\ManyToOne(targetEntity="Department")
	 * @ORM_Mapping\JoinColumn(name="department_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $department = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="apply_type", type="string", length=20)
	 */
	protected $apply_type;

	/**
	 * @var \Application\DeskPRO\Entity\AgentTeam
	 * @ORM_Mapping\ManyToOne(targetEntity="AgentTeam")
	 * @ORM_Mapping\JoinColumn(name="agent_team_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $agent_team = null;

	/**
	 * @var \Application\DeskPRO\Entity\Usergroup
	 * @ORM_Mapping\ManyToOne(targetEntity="Usergroup")
	 * @ORM_Mapping\JoinColumn(name="usergroup_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $usergroup = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person = null;

	public function setAgentTeam($agent_team)
	{
		$this->setModelField('person', null);
		$this->setModelField('usergroup', null);
		$this->setModelField('agent_team', $agent_team);
		$this->setModelField('apply_type', self::TYPE_AGENT_TEAM);
	}

	public function setPerson($person)
	{
		$this->setModelField('person', $person);
		$this->setModelField('usergroup', null);
		$this->setModelField('agent_team', null);
		$this->setModelField('apply_type', self::TYPE_PERSON);
	}

	public function setUsergroup($usergroup)
	{
		$this->setModelField('person', null);
		$this->setModelField('usergroup', $usergroup);
		$this->setModelField('agent_team', null);
		$this->setModelField('apply_type', self::TYPE_USERGROUP);
	}

	public function getWho()
	{
		switch ($this->apply_type) {
			case self::TYPE_PERSON:
				return $this->person;
				break;
			case self::TYPE_AGENT_TEAM:
				return $this->agent_team;
				break;
			case self::TYPE_USERGROUP:
				return $this->usergroup;
				break;
		}

		return null;
	}
}
