<?php

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;
use Orb\Util\CheckedOptionsArray;

/**
 * Class AddApproval
 *
 * @package Application\DeskPRO\Tickets\Actions
 */
class AddApproval extends AbstractContainerAwareAction implements ActionInterface
{
    /**
     * @var TicketApproval
     */
    protected $approval;

    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('approval_template_id', 'description');

        return $options;
    }

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $approvalTemplateId = $this->getActionOption('approval_template_id');
        $description = $this->getActionOption('description');

        $template = $this->getTemplateById($approvalTemplateId);

        if (!$template) {
            return;
        }

        $this->approval = TicketApproval::createFromTemplate($template);

        $this->approval->setTicket($ticket);
        $this->approval->setDescription($description);

        $manager = $this->getContainer()->get('approval.approval_manager');

        $manager->saveApproval(
            $this->approval,
            $manager->createContext($context->getEventMethod(), $context->getPersonContext())
        );
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
     * @return TicketApproval
     */
    public function getApproval()
    {
        return $this->approval;
    }
}
