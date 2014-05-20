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
 */

namespace Application\DeskPRO\Departments;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Validator\HasValidationMetadataInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

class ChatDepartmentEdit implements HasValidationMetadataInterface
{
	/**
	 * @var \Application\DeskPRO\Entity\Department
	 */

	public $department;

	/**
	 * @var \Application\DeskPRO\Entity\Department
	 */

	public $move_department;

	/**
	 * @var array
	 */

	public $permissions;

	/**
	 * @var \Application\DeskPRO\Entity\Department|null
	 */

	private $old_parent;

	public function __construct(Department $department)
	{
		$this->department  = $department;
		$this->permissions = new ArrayCollection(); // this is needed for proper validation of 'permissions'

		if ($department->parent) {

			$this->old_parent = $department->parent;
		}
	}

	/**
	 * @param EntityManager $em
	 */

	public function save(EntityManager $em)
	{
		$em->persist($this->department);
		$em->flush();
	}


	/**
	 * @param EntityManager $em
	 * @param \Application\DeskPRO\Entity\Person[] $agents
	 * @param \Application\DeskPRO\Entity\Usergroup[] $groups
	 */

	public function savePermissions(EntityManager $em, array $agents, array $groups)
	{
		$matrix = new DepartmentPermissionMatrix($agents, $groups);
		$matrix->setPermArray($this->permissions);
		$matrix->save($this->department, $em);
	}

	############################################################################
	# Validation Metadata
	############################################################################

	/**
	 * @param ExecutionContextInterface $context
	 */

	public function validateChangingOfParent(ExecutionContextInterface $context)
	{
		if(!$this->old_parent) {

			if (sizeof($this->department->getChildren()) > 0 && $this->department->getParentId() != 0) {

				$context->addViolationAt(
					'edit_department',
					'department.edit_chat.changing_parent_when_have_children'
				);
			}
		}
	}

	/**
	 * @param ValidatorClassMetadata $metadata
	 */

	public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
	{
		$metadata->addConstraint(
			new Callback(
				array(
					 'methods' => array('validateChangingOfParent')
				)
			)
		);
	}
}