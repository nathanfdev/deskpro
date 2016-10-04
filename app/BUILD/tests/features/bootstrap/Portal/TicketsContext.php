<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DpBehat\Portal;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use DpBehat\Data\DataContext;

class TicketsContext extends BasePortalContext
{
    /**
     * @var Ticket
     */
    private $last_ticket;

    /**
     * @When I go to the tickets page
     */
    public function iGoToTheTicketsPage()
    {
        $this->getPage('View Tickets')->open();
    }

    /**
     * @Then I should see my tickets
     */
    public function iShouldSeeMyTickets()
    {
    }

    /**
     * @Then I should see my ticket
     */
    public function iShouldSeeMyTicket()
    {
    }

    /**
     * @Given :who has/have a ticket
     */
    public function hasATicket($who)
    {
        if ($who === 'I') {
            $who = 'user';
        }

        $ticket = $this->get('ticket_manager')->createTicket();
        $person = $this->get('user_details')->getWho($who);

        $ticket->setPerson($person);
        $ticket->setSubject('subject');

        $msg = new TicketMessage();
        $msg->setPerson($person);
        $msg->setMessageText('message');
        $ticket->addMessage($msg);

        $this->persistAndFlush($ticket);

        $this->last_ticket = $ticket;

        DataContext::setReference('ticket', $ticket);
        DataContext::setReference('ticket_message', $msg);
    }

    /**
     * @Given I view my ticket
     */
    public function iTryToVisitThatTicket()
    {
        $this->getPage('Ticket')->open(['id' => $this->last_ticket->getId()]);
    }
}
