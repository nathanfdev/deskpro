<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Approval;

use Symfony\Component\Validator\Constraint;

/**
 * Class ApproverCriteria
 *
 * @package DeskPRO\Bundle\AppBundle\Validator\Constraints\Approval
 *
 * @Annotation
 */
class ApproverCriteria extends Constraint
{
    const APPROVER_CRITERIA = 'invalid_approver_criteria';

    public $invalidObjectMessage = 'Object passed must be of type ApproverCriteria';
    public $mustProvideAtLeastOneCriteriaMessage = 'At least one approver criteria must be provided';
    public $invalidAgentListMessage = 'Invalid list of agents';
    public $invalidUserListMessage = 'Invalid list of users';
    public $invalidTeamListMessage = 'Invalid list of teams';
    public $invalidDepartmentListMessage = 'Invalid list of departments';
    public $ifCannotChooseApproversMustDefineApproversMessage = 'If an agent cannot choose approvers, then agent and/or user approvers must be supplied';
}
