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
