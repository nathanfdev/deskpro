<?php

namespace DpTest\Bundle\AppBundle\TicketFilters\Diff;

use DeskPRO\Bundle\AppBundle\TicketFilters\Diff\TicketChange;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;

class TicketChangeTest extends \PHPUnit_Framework_TestCase
{
    private function getTickets()
    {
        $ticketA            = new TicketModel();
        $ticketA->id        = 0;
        $ticketA->followers = [1, 2];
        $ticketA->labels    = ['foo'];

        $ticketB                 = new TicketModel();
        $ticketB->id             = 2;
        $ticketB->followers      = [1, 2, 3];
        $ticketB->labels         = ['foo', 'bar'];
        $ticketB->new_messages[] = 'foo';

        return [$ticketA, $ticketB];
    }

    public function testChangedFields()
    {
        list($ticketA, $ticketB) = $this->getTickets();

        $ticketChange = new TicketChange($ticketA, $ticketB);

        $this->assertEquals(
            ['ticket.id', 'ticket.followers', 'ticket.labels', 'ticket.is_new', 'ticket.new_messages'],
            $ticketChange->getChangedFields()
        );
    }

    public function testHasChange()
    {
        list($ticketA, $ticketB) = $this->getTickets();

        $ticketChange = new TicketChange($ticketA, $ticketB);

        $this->assertEquals(true, $ticketChange->hasChange('ticket.id'));
        $this->assertEquals(true, $ticketChange->hasChange('ticket.followers'));
        $this->assertEquals(false, $ticketChange->hasChange('ticket.slas'));
    }

    public function testIsNew()
    {
        list($ticketA, $ticketB) = $this->getTickets();

        $ticketChange = new TicketChange($ticketA, $ticketB);

        $this->assertTrue($ticketChange->isNewTicket());
    }

    public function testIsNewMessage()
    {
        list($ticketA, $ticketB) = $this->getTickets();

        $ticketChange = new TicketChange($ticketA, $ticketB);

        $this->assertTrue($ticketChange->isNewMessage());
    }

    public function testIsPermChange()
    {
        list($ticketA, $ticketB) = $this->getTickets();

        $ticketChange = new TicketChange($ticketA, $ticketB);

        $this->assertTrue($ticketChange->isPermChange());
    }
}
