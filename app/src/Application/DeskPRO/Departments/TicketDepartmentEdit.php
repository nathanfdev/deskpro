<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

class TicketDepartmentEdit implements HasValidationMetadataInterface
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
		$this->department = $department;

		if ($department->parent) {
			$this->old_parent = $department->parent;
		}
	}

	/**
	 * @return bool
	 */
	private function doesNeedMove()
	{
		$old = $this->old_parent;
		$new = $this->department->parent;

		// No parent, nothing to verify
		if (!$new) {
			return false;
		// Not changed, nothing to verify
		} else if ( ($old && $new && $old == $new) || (!$old && !$new)) {
			return false;
		// New enabled
		} else if (!$old && $new) {
			return true;

		// Changed
		} else if ($old != $new) {
			return true;
		}

		return false;
	}


	/**
	 * @param EntityManager $em
	 */
	public function save(EntityManager $em)
	{
		// New, we should set a proper display order
		if (!$this->department->id) {
			$do = $em->getConnection()->fetchColumn("
				SELECT display_order
				FROM departments
				WHERE is_tickets_enabled = 1
				ORDER BY display_order DESC
			");
			$do += 10;
			$this->department->display_order = $do;
		}

		$em->persist($this->department);
		$em->flush();

		// Make sure parent doesnt have a trigger
		if ($this->department->parent) {
			$em->getConnection()->delete('ticket_triggers', array('department_id' => $this->department->parent->id));
		}
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

	/**
	 * @param EntityManager $em
	 */
	public function clearTrigger(EntityManager $em)
	{
		$triggers = $em->createQuery("
			SELECT trigger
			FROM DeskPRO:TicketTrigger trigger
			WHERE trigger.department = ?0
		")->setParameters(array($this->department))->execute();

		foreach ($triggers as $t) {
			$em->remove($t);
		}
		$em->flush();
	}

	############################################################################
	# Validation Metadata
	############################################################################

	public function validateParent(ExecutionContextInterface $context)
	{
		if ($this->doesNeedMove()) {
			if (!$this->old_parent) {
				$context->addViolationAt('move_department', 'Setting a new parent, must specify new department to move existing tickets to');
			} else if (count($this->old_parent->children)) {
				$context->addViolationAt('move_department', 'New department must not be a parent itself');
			}
		}
	}

	public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
	{
		// Symfony\Component\Validator\Exception\ConstraintDefinitionException:
		// The constraint Symfony\Component\Validator\Constraints\Callback cannot be put on properties or getters

		$metadata->addConstraint(new Callback(array(
			'methods' => array('validateParent')
		)));
	}
}