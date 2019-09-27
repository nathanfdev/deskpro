<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Approval;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
use DeskPRO\Bundle\AppBundle\Form\Type\Approval\BaseApprovalType;
use Doctrine\ORM\EntityManagerInterface;
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
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * ApprovalThresholdsValidator constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

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
            } else {
                $selectionCriteria = $template->getApproverSelectionCriteria();

                $approvers = $value->getApproversCount();
                $minNumberOfApprovers = $selectionCriteria->getMinNumberOfApprovers();
            }
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
                $personRepo = $this->em->getRepository(Person::class);
                $selectedApprovers = $value->getSelectedApprovers();
                $approvers = count($selectedApprovers->getPeople());

                if ($selectedApprovers->hasTicketUser()) {
                    $approvers ++;
                }

                if ($selectedApprovers->hasOrganizationManagers()) {
                    $approvers += $personRepo->countOrganizationManagers();
                }

                if ($selectedApprovers->hasAllAgents()) {
                    $approvers += $personRepo->countAgents();
                }
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
