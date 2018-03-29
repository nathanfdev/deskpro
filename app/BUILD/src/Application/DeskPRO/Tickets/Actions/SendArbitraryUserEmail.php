<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\EmailGateway\PersonFromEmailProcessor;
use Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\TicketEmailBuilder;
use Orb\Util\CheckedOptionsArray;

/**
 * Send an email to the user.
 *
 * @option bool     template          The template to send
 * @option bool     from_name         Who to send the email from
 * @option bool     from_account      The account to send from (falsey for ticket account)
 * @option string[] emails            Email addresses to send to
 * @option bool     send_org_managers True to send to all org managers
 */
class SendArbitraryUserEmail extends AbstractEmailAction
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames('template', 'from_name', 'from_account', 'emails', 'send_org_managers', 'single_email', 'headers');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $context->getLogger()->debug('[SendArbitraryUserEmail] Begin');
        $startTime = microtime(true);

        try {
            $fromAccount = $this->getFromEmailAccountOption($ticket, $context);
        } catch (\InvalidArgumentException $e) {
            $context->getLogger()->warn("[SendArbitraryUserEmail] Error {$e->getMessage()}");

            return;
        }

        try {
            $template = $this->getEmailTemplateOption($ticket, $context, false);
        } catch (\InvalidArgumentException $e) {
            $context->getLogger()->warn("[SendArbitraryUserEmail] Error {$e->getMessage()}");

            return;
        }

        //-------------------------
        // Vars
        //-------------------------

        $defaultVars = $this->getStandardEmailVars($ticket, $context, 'user');

        //-------------------------
        // Sort out the users to send to
        //-------------------------

        /** @var \Application\DeskPRO\Entity\Person[] $sendPeople */
        $sendPeople = [];

        if ($this->getActionOption('send_org_managers')) {
            /** @var \Application\DeskPRO\EntityRepository\Organization $orgRepo */
            $orgRepo  = $this->getContainer()->getEm()->getRepository(Organization::class);
            $managers = $orgRepo->getManagers($ticket->getOrganization());

            foreach ($managers as $p) {
                $sendPeople[$p->getEmailAddress()] = $p;
            }
        }

        $regClosed = !$this->getContainer()->getSetting('core.reg_enabled');
        foreach ($this->getActionOption('emails') as $email) {
            $person = $this->getContainer()->getEm()->getRepository(Person::class)->findOneByEmail($email);
            if ($person) {
                $sendPeople[$email] = $person;
            } else {
                if ($regClosed) {
                    continue;
                }
                $personProcessor = new PersonFromEmailProcessor();

                $eml        = new EmailAddress();
                $eml->email = $email;
                $person     = $personProcessor->createPerson($eml);

                if ($person) {
                    $sendPeople[$email] = $person;
                }
            }
        }

        if (!$sendPeople) {
            $context->getLogger()->debug('[SendArbitraryUserEmail] no people to send to');
        }

        //-------------------------
        // Send emails
        //-------------------------

        foreach ($sendPeople as $email => $person) {
            $context->getLogger()->debug(sprintf('[SendArbitraryUserEmail] Sending to Person#%d %s <%s>', $person->id, $person->getDisplayName(), $person->primary_email ? $person->primary_email->email : '?'));

            $builder = TicketEmailBuilder::createFromContainer($this->getContainer())
                ->setTicket($ticket)
                ->setToPerson($person)
                ->setToPersonEmail($email)
                ->setUserMode()
                ->setTemplateName($template)
                ->setFromName($this->renderFromName($this->getActionOption('from_name'), $ticket, $context, 'user'))
                ->setMaxAttachSize(0)
                ->setLogger($context->getLogger())
                ->setHeaders($this->processHeaders($this->getActionOption('headers', []), $ticket, $context))
                ->setFromEmailAccount($fromAccount);

            $ticketEmail = $builder->buildTicketEmail();
            $defaultVars = array_merge($defaultVars, $builder->getCommonVars($person->isAgent()));

            try {
                $ticketEmail->send($defaultVars);
                $this->recordEmailTicketLog($ticketEmail, $ticket, $context);
            } catch (\Exception $e) {
                $context->getLogger()->error(sprintf('Exception: [%s] %s', $e->getCode(), $e->getMessage()), ['exception' => $e]);
                throw $e;
            }

            $context->getLogger()->info(sprintf('[SendArbitraryUserEmail] Sent message in %.3fs', microtime(true) - $startTime));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$this->getContainer()->getEmailAccountManager()->countOutgoingAccounts()) {
            $context->getLogger()->debug('[SendArbitraryUserEmail] no outgoing email accounts are defined');

            return true;
        }

        if ($context->getVars()->get('mute_user_emails')) {
            $context->getLogger()->debug('[SendArbitraryUserEmail] mute_user_emails = true');

            return true;
        }

        return false;
    }
}
