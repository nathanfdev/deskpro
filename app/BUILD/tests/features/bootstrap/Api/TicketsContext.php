<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace DpBehat\Api;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\TicketManager;
use Doctrine\ORM\EntityManager;
use DpBehat\BaseContext;

/**
 * Class TicketsContext.
 */
class TicketsContext extends BaseContext
{
    /**
     * @var int|null
     */
    public static $ticketId = null;

    /**
     * @Given there is an unassigned ticket with ID referenced as ticketId
     */
    public function thereIsAnUnassignedTicket()
    {
        $this->createTicket(uniqid('Ticket '), null);
    }

    /**
     * @Given I create a ticket and reference its' ID as ticketId
     */
    public function iCreateATicketAndReferenceItsId()
    {
        $this->createTicket(uniqid('Ticket '), AuthContext::$user->is_agent ? AuthContext::$user : null);
    }

    /**
     * @Given I create a ticket with subject ":subject" and reference its' ID as ticketId
     */
    public function iCreateATicketWithSubjectAndReferenceItsId($subject)
    {
        $this->createTicket($subject, AuthContext::$user->is_agent ? AuthContext::$user : null);
    }

    /**
     * @param string $subject
     * @param $agent
     *
     * @throws \Exception
     */
    private function createTicket($subject, $agent)
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.default_entity_manager');
        if ($agent) {
            $agent = $em->merge($agent);
        }
        $user = $em->merge(AuthContext::$user);

        $ticket = new Ticket();
        $ticket->setSubject($subject);
        if ($agent) {
            $ticket->setAgent($agent);
        }

        /** @var TicketManager $manager */
        $manager = $this->get('ticket_manager');
        $context = $manager->createAgentExecutorContext($user, 'new', 'api');
        $ticket->disableAutoTicketProcess();
        $manager->saveTicket($ticket, $context);

        self::$ticketId = $ticket->getId();
    }
}
