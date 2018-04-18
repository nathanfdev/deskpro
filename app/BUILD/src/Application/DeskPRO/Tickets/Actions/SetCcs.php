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
 * Adds and removed CC'ed users to the ticket, creating users as necessary.
 *
 * @option string[] add_emails       Array of email addresses of users to add
 * @option string[] remove_emails    Array of email addresses of users to remove
 * @option bool     add_org_managers True to add all org managers
 */
class SetCcs extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames('add_emails', 'remove_emails', 'add_org_managers');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        //------------------------------
        // Add org managers
        //------------------------------

        if ($this->getActionOption('add_org_managers') && $ticket->organization) {
            $managers = $this->getContainer()->getEm()->getRepository('DeskPRO:Organization')->getManagers($ticket->organization);
            foreach ($managers as $manager) {
                if (!$ticket->hasParticipantPerson($manager)) {
                    $context->getLogger()->debug(sprintf('[SetCcs] Adding org manager %d %s %s', $manager->id, $manager->getDisplayName(), $manager->primary_email->email));
                    $ticket->addParticipantPerson($manager);
                }
            }
        }

        //------------------------------
        // Add people
        //------------------------------

        $account_manager = $this->getContainer()->getEmailAccountManager();
        $reg_closed      = !$this->getContainer()->get('dp_authentication_manager.user')->isRegistrationFormVisible();
        if ($this->getActionOption('add_emails')) {
            foreach ($this->getActionOption('add_emails') as $email) {
                $email = trim($email);

                if (!$email) {
                    continue;
                }
                if ($ticket->hasParticipantEmailAddress($email)) {
                    continue;
                }
                if (!StringEmail::isValueValid($email)) {
                    $context->getLogger()->debug(sprintf('[SetCcs] Skipping %s because invalid email', $email));
                    continue;
                }
                if ($account_manager->findAccountForEmailAddress($email)) {
                    $context->getLogger()->debug(sprintf('[SetCcs] Skipping %s because email is an email account', $email));
                    continue;
                }

                $person = $this->getContainer()->getEm()->getRepository('DeskPRO:Person')->findOneByEmail($email);
                if ($person) {
                    $context->getLogger()->debug(sprintf('[SetCcs] Adding user %d %s %s', $person->id, $person->getDisplayName(), $person->primary_email->email));
                    $ticket->addParticipantPerson($person);
                } else {
                    if ($reg_closed) {
                        $context->getLogger()->debug(sprintf('[SetCcs] Unknown user and reg is closed, skipping %s', $email));
                        continue;
                    }
                    $person_processor = new PersonFromEmailProcessor();

                    $eml        = new EmailAddress();
                    $eml->email = $email;
                    $person     = $person_processor->createPerson($eml, true);

                    if ($person) {
                        $context->getLogger()->debug(sprintf('[SetCcs] Adding NEW user %d %s %s', $person->id, $person->getDisplayName(), $person->primary_email->email));
                        $ticket->addParticipantPerson($person);
                    }
                }
            }
        }

        //------------------------------
        // Remove people
        //------------------------------

        if ($this->getActionOption('remove_emails')) {
            foreach ($this->getActionOption('remove_emails') as $email) {
                $email = trim($email);

                foreach ($ticket->participants as $p) {
                    if ($p->person->findEmailAddress($email)) {
                        $context->getLogger()->debug(sprintf('[SetCcs] Removing user %d %s %s', $p->person->id, $p->person->getDisplayName(), $p->person->primary_email->email));
                        $ticket->removeParticipantPerson($p->person);
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'cc')) {
            return ['cc'];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $this->applyAction($ticket, $context);
    }
}
