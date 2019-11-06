<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Approval;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

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
     *
     * @throws UnexpectedTypeException
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ApprovalThresholds) {
            throw new UnexpectedTypeException($constraint, ApprovalThresholds::class);
        }

        $approvers            = null;
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
                $selectionCriteria    = $value->getApproverSelectionCriteria();
                $minNumberOfApprovers = $selectionCriteria->getMinNumberOfApprovers();

                if (
                    !$selectionCriteria->canSelectOrganizationManagers() &&
                    !$selectionCriteria->canSelectFromAllAgents()
                ) {
                    // Don't need to validate until the actual approval is created if approvers are inferred
                    $approvers = count($selectionCriteria->getSelectFromPeople());

                    if ($selectionCriteria->canSelectTicketUser()) {
                        ++$approvers;
                    }

                    // Make sure we have enough approvers to meet the minimum threshold
                    if ($approvers < $minNumberOfApprovers) {
                        $this
                            ->context
                            ->buildViolation(sprintf($constraint->minNumberOfApproversMessage, $approvers))
                            ->addViolation()
                        ;
                    }
                }

                if ($minNumberOfApprovers < $value->getRequiredApprovals() || $minNumberOfApprovers < $value->getRequiredRejections()) {
                    /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
                    $context = $this->context;
                    $context
                        ->buildViolation($constraint->minNumberOfApproversGreaterThanToApproveOrReject)
                        ->setCode(ApprovalThresholds::MIN_NUMBER_OF_APPROVERS_GREATER_THAN_TO_APPROVE_OR_REJECT)
                        ->addViolation()
                    ;

                    return;
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

        if ($approvers !== null && $approvers < max($thresholds)) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
