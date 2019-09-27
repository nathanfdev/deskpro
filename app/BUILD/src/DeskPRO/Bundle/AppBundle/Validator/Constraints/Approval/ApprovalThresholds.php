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

    public $message = 'You must provide a number of required approvals or rejections, that does not exceed the number of approvers';
    public $minNumberOfApproversMessage = 'There aren\'t enough approvers to meet the specified minimum number of required approvers';
    public $atLeastOneApproverMustBeSelectedMessage = 'You must specify a minimum of 1 approver';
}
