<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Approval;

use Symfony\Component\Validator\Constraint;

/**
 * Class ApprovalThresholds.
 *
 * @Annotation
 */
class ApprovalThresholds extends Constraint
{
    const APPROVAL = 'invalid_approval_thresholds';

    const MIN_NUMBER_OF_APPROVERS_GREATER_THAN_TO_APPROVE_OR_REJECT = 'min_number_of_approvers_greater_than_to_approve_or_reject';

    public $message                                          = 'You must provide a number of required approvals or rejections, that does not exceed the number of approvers';
    public $minNumberOfApproversMessage                      = 'There aren\'t enough approvers to meet the specified minimum number of required approvers';
    public $atLeastOneApproverMustBeSelectedMessage          = 'You must specify a minimum of 1 approver';
    public $minNumberOfApproversGreaterThanToApproveOrReject = 'Minimum number of approvers value must not be greater than To approve or To Reject values';
}
