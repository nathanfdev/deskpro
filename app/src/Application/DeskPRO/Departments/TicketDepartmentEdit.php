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
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

class TicketDepartmentEdit
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

	############################################################################
	# Validation Metadata
	############################################################################

	public function validateParent(ExecutionContextInterface $context)
	{
		$old = $this->old_parent;
		$new = $this->department->parent;

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
		$metadata->addPropertyConstraint('move_department', new Callback(array(
			'methods' => array('validateParent')
		)));
	}
}