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
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Tickets\Triggers\TriggerActions;
use Application\DeskPRO\Tickets\Triggers\TriggerTerms;
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
		$em->persist($this->department);
		$em->flush();

		// Make sure parent doesnt have a trigger
		if ($this->department->parent) {
			$trigger = $em->createQuery("
				SELECT trigger
				FROM DeskPRO:TicketTrigger trigger
				WHERE trigger.department = ?0
			")->setParameters(array($this->department->parent))->getOneOrNullResult();

			if ($trigger) {
				$em->remove($trigger);
				$em->flush();
			}
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
	 * @param array         $trigger_actions
	 * @return TicketTrigger
	 */
	public function saveTrigger(EntityManager $em, array $trigger_actions)
	{
		$trigger = $em->createQuery("
			SELECT trigger
			FROM DeskPRO:TicketTrigger trigger
			WHERE trigger.department = ?0
		")->setParameters(array($this->department))->getOneOrNullResult();

		if (!$trigger_actions) {
			if ($trigger) {
				$em->remove($trigger);
				$em->flush();
			}
			return null;
		}

		if (!$trigger) {
			$trigger = new TicketTrigger();
			$trigger->department = $this->department;
			$trigger->event_trigger = 'newticket';
			$trigger->by_agent_mode = array('api', 'web');
			$trigger->by_user_mode  = array('api', 'form', 'portal', 'widget');
		}

		$actions = new TriggerActions();
		try {
			$actions->importFromArray(array('actions' => $trigger_actions));
		} catch (\Exception $e) {}

		$terms = new TriggerTerms();
		$terms->addTermFromArray(array(
			'type'    => 'CheckDepartment',
			'op'      => 'is',
			'options' => array('department_ids' => array($this->department->id))
		));

		$trigger->title     = "New Ticket";
		$trigger->run_order = -100;
		$trigger->actions   = $actions;
		$trigger->terms     = $terms;

		$em->persist($trigger);
		$em->flush($trigger);

		return $trigger;
	}


	/**
	 * @param EntityManager $em
	 */
	public function clearTrigger(EntityManager $em)
	{
		$trigger = $em->createQuery("
			SELECT trigger
			FROM DeskPRO:TicketTrigger trigger
			WHERE trigger.department = ?0
		")->setParameters(array($this->department))->getOneOrNullResult();

		if ($trigger) {
			$em->remove($trigger);
			$em->flush();
		}
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