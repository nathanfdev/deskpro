<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\EmailGateway\PersonFromEmailProcessor;
use Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\TicketEmailBuilder;
use Orb\Util\CheckedOptionsArray;

/**
 * Send an email to the user
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
     * {@inheritDoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames('template', 'from_name', 'from_account', 'emails', 'send_org_managers', 'single_email', 'headers');

        return $options;
    }


    /**
     * {@inheritDoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $context->getLogger()->debug("[SendArbitraryUserEmail] Begin");
        $start_time = microtime(true);

        try {
            $from_account = $this->getFromEmailAccountOption($ticket, $context);
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

        #-------------------------
        # Vars
        #-------------------------

        $default_vars = $this->getStandardEmailVars($ticket, $context, 'user');

        #-------------------------
        # Sort out the users to send to
        #-------------------------

        /** @var \Application\DeskPRO\Entity\Person[] $send_people */
        $send_people = array();

        if ($this->getActionOption('send_org_managers')) {
            $managers = $this->getContainer()->getEm()->getRepository('DeskPRO:Organization')->getManagers($ticket->organization);
            foreach ($managers as $p) {
                $send_people[] = $p;
            }
        }

        $reg_closed = !$this->getContainer()->getSetting('core.reg_enabled');
        foreach ($this->getActionOption('emails') as $email) {
            $person = $this->getContainer()->getEm()->getRepository('DeskPRO:Person')->findOneByEmail($email);
            if ($person) {
                $send_people[] = $person;
            } else {
                if ($reg_closed) {
                    continue;
                }
                $person_processor = new PersonFromEmailProcessor();

                $eml = new EmailAddress();
                $eml->email = $email;
                $person = $person_processor->createPerson($eml, true);

                if ($person) {
                    $send_people[] = $person;
                }
            }
        }

        $send_people = array_unique($send_people);

        if (!$send_people) {
            $context->getLogger()->debug("[SendArbitraryUserEmail] no people to send to");
        }

        #-------------------------
        # Send emails
        #-------------------------

        foreach ($send_people as $person) {
            $context->getLogger()->debug(sprintf("[SendArbitraryUserEmail] Sending to Person#%d %s <%s>", $person->id, $person->getDisplayName(), $person->primary_email->email));

            $build = TicketEmailBuilder::createFromContainer($this->getContainer())
                ->setTicket($ticket)
                ->setToPerson($person)
                ->setUserMode()
                ->setTemplateName($template)
                ->setFromName($this->renderFromName($this->getActionOption('from_name'), $ticket, $context, 'user'))
                ->setMaxAttachSize(0)
                ->setLogger($context->getLogger())
                ->setHeaders($this->processHeaders($this->getActionOption('headers', array()), $ticket, $context))
                ->setFromEmailAccount($from_account);

            $ticket_email = $build->buildTicketEmail();

            try {
                $ticket_email->send($default_vars);
                $this->recordEmailTicketLog($ticket_email, $ticket, $context);
            } catch (\Exception $e) {
                $context->getLogger()->error(sprintf("Exception: [%s] %s", $e->getCode(), $e->getMessage()), array('exception' => $e));
                throw $e;
            }

            $context->getLogger() ->info(sprintf("[SendArbitraryUserEmail] Sent message in %.3fs", microtime(true) - $start_time));
        }
    }


    /**
     * {@inheritDoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$this->getContainer()->getEmailAccountManager()->countOutgoingAccounts()) {
            $context->getLogger()->debug("[SendArbitraryUserEmail] no outgoing email accounts are defined");

            return true;
        }

        if ($context->getVars()->get('mute_user_emails')) {
            $context->getLogger()->debug("[SendArbitraryUserEmail] mute_user_emails = true");

            return true;
        }

        return false;
    }
}
