<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\EmailGateway\PersonFromEmailProcessor;
use Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;
use Orb\Validator\StringEmail;

/**
 * Sets the user owner of a ticket.
 *
 * @option int email_address
 * @option bool add_cc
 */
class SetUserOwner extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('email_address');
        $options->addValidNames('add_cc');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $user_email = $this->getActionOption('email_address');
        $reg_closed = !$this->getContainer()->get('dp_authentication_manager.user')->isRegistrationFormVisible();
        $person     = $this->getContainer()->getEm()->getRepository('DeskPRO:Person')->findOneByEmail($user_email);

        if (!$person) {
            if ($reg_closed) {
                return;
            }
            $person_processor = new PersonFromEmailProcessor();

            $eml        = new EmailAddress();
            $eml->email = $user_email;
            $person     = $person_processor->createPerson($eml);
        }

        $orig_person = $ticket->person;

        if ($person) {
            $ticket->person       = $person;
            $ticket->person_email = null;

            if ($this->getActionOption('add_cc')) {
                if (!$ticket->hasParticipantPerson($orig_person)) {
                    $ticket->addParticipantPerson($orig_person);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        $user_email = $this->getActionOption('email_address');
        if (!StringEmail::isValueValid($user_email)) {
            return true;
        }
        if ($ticket->person->hasEmailAddress($user_email)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $set_agent_id = $this->getActionOption('agent_id');
        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'assign_agent')) {
            if ($set_agent_id == $person->getId()) {
                if ($person->PermissionsManager->TicketChecker->canModify($ticket, 'assign_self')) {
                    return;
                }

                return ['assign_self'];
            }

            return ['assign_agent'];
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
