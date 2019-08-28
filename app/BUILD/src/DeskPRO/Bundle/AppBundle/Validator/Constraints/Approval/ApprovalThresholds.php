<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Approval;

use Symfony\Component\Validator\Constraint;

/**
 * Class ApprovalThresholds
 *
 * @package DeskPRO\Bundle\AppBundle\Validator\Constraints\Approval
 *
 * @Annotation
 */
class ApprovalThresholds extends Constraint
{
    const APPROVAL = 'invalid_approval_thresholds';

    public $message = 'There aren\'t enough approvers to meet the approval/rejection thresholds';
}
