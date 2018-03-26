<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Set the status.
 *
 * @option string status
 */
class SetStatus extends AbstractAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('status');

        $me = $this;
        $options->addCallbackCheckedOption('status', function ($v) use ($me) {
            return $me->isValidStatus($v);
        });

        return $options;
    }

    /**
     * @param string $status
     *
     * @return bool
     */
    public function isValidStatus($status)
    {
        static $valid_statuses = [
            'awaiting_agent', 'awaiting_user', 'resolved', 'archived',
            'hidden.spam', 'hidden.deleted',
        ];

        return in_array($status, $valid_statuses);
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $set_status = $this->getActionOption('status');
        if (!$this->isValidStatus($set_status)) {
            return;
        }

        $ticket->setStatus($set_status);
        $context->getLogger()->debug("[SetStatus] Setting status $set_status");
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        $set_status = $this->getActionOption('status');
        if ($ticket->getStatusCode() == $set_status || !$this->isValidStatus($set_status)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $set_status = $this->getActionOption('status');
        if (($set_status == 'hidden.deleted' || $set_status == 'hidden.spam') && !$person->PermissionsManager->TicketChecker->canDelete($ticket)) {
            return ['delete'];
        }
        if ($set_status == 'awaiting_agent' && !$person->PermissionsManager->TicketChecker->canModify($ticket, 'set_awaiting_agent')) {
            return ['set_awaiting_agent'];
        }
        if ($set_status == 'awaiting_user' && !$person->PermissionsManager->TicketChecker->canModify($ticket, 'set_awaiting_user')) {
            return ['set_awaiting_user'];
        }
        if ($set_status == 'resolved' && !$person->PermissionsManager->TicketChecker->canModify($ticket, 'set_resolved')) {
            return ['set_resolved'];
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $this->applyAction($ticket, $context);
    }
}
