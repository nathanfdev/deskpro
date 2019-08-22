<?php

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;

/**
 * Class AddApprovalAction
 *
 * @package Application\DeskPRO\Tickets\TicketActions
 */
class AddApprovalAction extends AbstractAction
{
    /**
     * @var string|int
     */
    protected $approval_template_id;

    /**
     * AddApprovalAction constructor.
     *
     * @param string|int $approval_template_id
     */
    public function __construct($approval_template_id)
    {
        $this->approval_template_id = $approval_template_id;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(Ticket $ticket)
    {
        // todo: add approval
    }

    /**
     * {@inheritDoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        return [
            ['action' => 'add_approval', 'approval_template_id' => $this->approval_template_id],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function merge(ActionInterface $otherAction)
    {
        return $otherAction;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription($as_html = true)
    {
        /** @var ApprovalTemplate $template */
        $template = App::getContainer()->getEm()->getRepository(ApprovalTemplate::class)->findOneBy([
            'id' => $this->approval_template_id,
        ]);

        if ($template) {
            return $as_html
                ? sprintf('Add approval: <span class="with-approval-template">%s</span>', htmlspecialchars($template->getName(), \ENT_QUOTES))
                : sprintf('Add approval: %s', $template->getName())
            ;
        }

        return 'Add approval: (unknown)';
    }
}
