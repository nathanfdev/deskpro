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
        $minNumberOfApprovers = null;

        if ($value instanceof AbstractBaseApproval) {
            $template = $value->getTemplate();
            if (!$template->canChooseApprovers()) {
                return;
            }

            $selectionCriteria = $template->getApproverSelectionCriteria();

            $approvers = $value->getApproversCount();
            $minNumberOfApprovers = $selectionCriteria->getMinNumberOfApprovers();
        } elseif ($value instanceof ApprovalTemplate) {
            if ($value->canChooseApprovers()) {
                $selectionCriteria = $value->getApproverSelectionCriteria();

                // Don't need to validate until the actual approval is created if approvers are inferred
                if ($selectionCriteria->canSelectTicketUser() ||
                    $selectionCriteria->canSelectOrganizationManagers() ||
                    $selectionCriteria->canSelectFromAllAgents()
                ) {
                    return;
                }

                $approvers = count($selectionCriteria->getSelectFromPeople());
                $minNumberOfApprovers = $selectionCriteria->getMinNumberOfApprovers();
            } else {
                $selectedApprovers = $value->getSelectedApprovers();
                $approvers = count($selectedApprovers->getPeople());
            }
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

        $minNumberOfApprovers = $minNumberOfApprovers ?: max($thresholds);

        if ($approvers < $minNumberOfApprovers) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
