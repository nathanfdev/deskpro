<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Approval;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ApprovalThresholdsValidator.
 */
class ApprovalThresholdsValidator extends ConstraintValidator
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $minNumberOfApprovers = null;

        if ($value instanceof AbstractBaseApproval) {
            $template = $value->getTemplate();
            if (!$template->canChooseApprovers()) {
                return;
            } else {
                $approvers = $value->getApproversCount();
            }
        } elseif ($value instanceof ApprovalTemplate) {
            if ($value->canChooseApprovers()) {
                $selectionCriteria = $value->getApproverSelectionCriteria();

                // Don't need to validate until the actual approval is created if approvers are inferred
                if (
                    $selectionCriteria->canSelectOrganizationManagers() ||
                    $selectionCriteria->canSelectFromAllAgents()
                ) {
                    return;
                }

                $approvers = count($selectionCriteria->getSelectFromPeople());

                if ($selectionCriteria->canSelectTicketUser()) {
                    ++$approvers;
                }

                $minNumberOfApprovers = $selectionCriteria->getMinNumberOfApprovers();

                // Make sure we have enough approvers to meet the minimum threshold
                if ($approvers < $minNumberOfApprovers) {
                    $this
                        ->context
                        ->buildViolation(sprintf($constraint->minNumberOfApproversMessage, $approvers))
                        ->addViolation()
                    ;
                }
            } else {
                $personRepo        = $this->em->getRepository(Person::class);
                $selectedApprovers = $value->getSelectedApprovers();
                $approvers         = count($selectedApprovers->getPeople());

                if ($selectedApprovers->hasTicketUser()) {
                    ++$approvers;
                }

                if ($selectedApprovers->hasOrganizationManagers()) {
                    $approvers += $personRepo->countOrganizationManagers();
                }

                if ($selectedApprovers->hasAllAgents()) {
                    $approvers += $personRepo->countAgents();
                }

                if (0 === $approvers) {
                    $this
                        ->context
                        ->buildViolation($constraint->atLeastOneApproverMustBeSelectedMessage)
                        ->addViolation()
                    ;

                    return;
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

        if ($approvers < max($thresholds)) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
