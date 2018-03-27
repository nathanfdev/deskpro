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
 * Set the email account.
 *
 * @option int email_account_id
 */
class SetEmailAccount extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('email_account_id');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $set_account_id = $this->getActionOption('email_account_id');

        if ($set_account_id) {
            try {
                $account = $this->getContainer()->getEmailAccountManager()->getActiveAccount($set_account_id);
            } catch (\OutOfBoundsException $e) {
                return false;
            }
        } else {
            $account = null;
        }

        $ticket->email_account = $account;
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        $set_account_id    = $this->getActionOption('email_account_id');
        $ticket_account_id = $ticket->email_account ? $ticket->email_account->id : 0;

        if ($ticket_account_id == $set_account_id) {
            return true;
        }

        if ($set_account_id) {
            if (!$this->getContainer()->getEmailAccountManager()->hasActiveAccount($set_account_id)) {
                return true; //invalid
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'fields')) {
            return ['fields'];
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
