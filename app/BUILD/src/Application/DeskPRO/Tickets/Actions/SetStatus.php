<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Set the status.
 *
 * @option string status
 */
class SetStatus extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $options = [])
    {
        // Have to overwrite constructor to set container before main initialization
        // we need container in isValidStatus that is called during object creation
        $this->setContainer(App::getContainer());

        parent::__construct($options);
    }

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
        $valid_statuses = array_map(function ($item) {
            return $item['value'];
        }, $this->getContainer()->getTicketStatuses()->getFormOptions());

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

        $ticket->setTicketStatus($this->getContainer()->getTicketStatuses()->findStatus($set_status, false, true));
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
        if ($this->isDeletedOrSpamStatus($set_status) && !$person->PermissionsManager->TicketChecker->canDelete($ticket)) {
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

    /**
     * @param string $statusCode
     *
     * @return bool
     */
    protected function isDeletedOrSpamStatus($statusCode)
    {
        $statuses = $this->getContainer()->getTicketStatuses();

        return in_array($statusCode, [
            $statuses->getDeletedStatus()->getStatusCode(),
            $statuses->getSpamStatus()->getStatusCode(),
        ]);
    }
}
