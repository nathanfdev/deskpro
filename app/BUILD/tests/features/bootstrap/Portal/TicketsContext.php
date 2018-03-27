<?php

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
