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
	 * The connected usergroup. If this is set, then person cannot be set.
	 *
	 * @var \Application\DeskPRO\Entity\Department
	 * @ORM_Mapping\ManyToOne(targetEntity="Usergroup")
	 * @ORM_Mapping\JoinColumn(name="usergroup_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $usergroup = null;

	/**
	 * The connected person. If this is set, then usergroup cannot be set.
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person = null;

	/**
	 * @ORM_Mapping\Column(name="app", type="string", length=50)
	 */
	protected $app;


	public function setUsergroup($ug)
	{
		$this->setModelField('usergroup', $ug);

		if ($ug !== null) {
			$this->person = null;
		}
	}

	public function setPerson($p)
	{
		$this->setModelField('person', $p);

		if ($p !== null) {
			$this->usergroup = null;
		}
	}
}
