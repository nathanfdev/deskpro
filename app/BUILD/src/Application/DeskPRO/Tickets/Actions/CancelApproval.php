<?php

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;
use DeskPRO\Bundle\AppBundle\Entity\Repository\ApprovalRepository;
use Orb\Util\CheckedOptionsArray;

/**
 * Class CancelApproval
 *
 * @package Application\DeskPRO\Tickets\Actions
 */
class CancelApproval extends AbstractContainerAwareAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('approval_template_id', 'all_approvals');

        return $options;
    }

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        if ($this->getActionOption('all_approvals')) {
            $approvals = $this->getApprovalRepository()->getTicketApprovalsByTicket($ticket);
        } else {
            $template = $this->getTemplateById($this->getActionOption('approval_template_id'));
            if (!$template) {
                return;
            }
            $approvals = $this->getApprovalRepository()->getTicketApprovalsByTicketAndTemplate($ticket, $template);
        }

        $approvalManager = $this->getContainer()->get('approval.approval_manager');

        /** @var TicketApproval $approval */
        foreach ($approvals as $approval) {
            if (!$approval->isStatus(TicketApproval::STATUS_PENDING)) {
                continue;
            }

            $approvalManager->cancelApproval(
                $approval,
                $approvalManager->createContext($context->getEventMethod(), $context->getPersonContext())
            );
        }
    }

    /**
     * @param int|string $id
     * @return ApprovalTemplate|null
     */
    private function getTemplateById($id)
    {
        if (empty($id)) {
            return null;
        }

        return $this->getContainer()->getEm()->getRepository(ApprovalTemplate::class)->find($id);
    }

    /**
     * @return ApprovalRepository
     */
    private function getApprovalRepository()
    {
        return $this->getContainer()->getEm()->getRepository(AbstractBaseApproval::class);
    }
}
