<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Approval;

use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class TicketApprovalOrgManagersValidator.
 */
class TicketApprovalOrgManagersValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     *
     * @throws UnexpectedTypeException
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof TicketApprovalOrgManagers) {
            throw new UnexpectedTypeException($constraint, TicketApprovalOrgManagers::class);
        }

        if (!$value) {
            return;
        }
        if (!$value instanceof TicketApproval) {
            throw new UnexpectedTypeException($value, TicketApproval::class);
        }

        $ticket = $value->getTicket();
        if (!$ticket) {
            return;
        }

        $person = $ticket->getPerson();
        if (!$person) {
            return;
        }

        $template = $value->getTemplate();
        if (!$template) {
            return;
        }

        $hasOrgManagers = false;
        if ($template->canChooseApprovers() && $template->getApproverSelectionCriteria()) {
            $hasOrgManagers = $template->getApproverSelectionCriteria()->canSelectOrganizationManagers();
        } elseif ($template->getSelectedApprovers()) {
            $hasOrgManagers = $template->getSelectedApprovers()->hasOrganizationManagers();
        }

        if (!$hasOrgManagers) {
            return;
        }

        $organization = $person->getOrganization();
        if (!$organization) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->userHasNoOrgMessage)
                ->setCode(TicketApprovalOrgManagers::USER_HAS_NO_ORG)
                ->addViolation()
            ;
        } elseif (!$organization->getManagers()->count()) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->orgHasNoManagersMessage)
                ->setCode(TicketApprovalOrgManagers::ORG_HAS_NO_MANAGERS)
                ->addViolation()
            ;
        }
    }
}
