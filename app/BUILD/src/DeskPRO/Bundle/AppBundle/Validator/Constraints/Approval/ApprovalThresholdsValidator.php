<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Approval;

use DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
use DeskPRO\Bundle\AppBundle\Form\Type\Approval\BaseApprovalType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ApprovalThresholdsValidator
 *
 * @package DeskPRO\Bundle\AppBundle\Validator\Constraints\Approval
 */
class ApprovalThresholdsValidator extends ConstraintValidator
{
    /**
     * @param BaseApprovalType|ApprovalTemplate
     * @param Constraint|ApprovalThresholds $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof AbstractBaseApproval) {
            $approvers = count($value->getApprovers());
        } elseif ($value instanceof ApprovalTemplate) {
            // If agent can choose approvers then we don't need to validate until approval is created from template
            if ($value->getApproverCriteria()->canChooseApprovers()) {
                return;
            }
            $approvers = count($value->getApproverCriteria()->getAgents());
            $approvers += count($value->getApproverCriteria()->getUsers());
        } else {
            return;
        }

        $thresholds = array_filter([
            $value->getRequiredApprovals(),
            $value->getRequiredRejections(),
        ]);

        if (empty($thresholds)) {
            return;
        }

        if ($approvers < min($thresholds)) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
