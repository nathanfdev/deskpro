<?php

namespace DpTest\Bundle\AppBundle\TicketFilters\Diff;

use DeskPRO\Bundle\AppBundle\TicketFilters\Diff\TicketChange;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\CustomData;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;

class TicketChangeTest extends \PHPUnit_Framework_TestCase
{
    private function getTickets()
    {
        $ticketA                = new TicketModel();
        $ticketA->id            = 0;
        $ticketA->followers     = [1, 2];
        $ticketA->labels        = ['foo'];
        $ticketA->custom_fields = [
            new CustomData(1, [10]),
            new CustomData(2, [20]),
            new CustomData(3, 'foo'),
            new CustomData(4, strtotime('2018-04-09 01:00:00')),
            new CustomData(5, 1),
        ];

        $ticketB                 = new TicketModel();
        $ticketB->id             = 2;
        $ticketB->followers      = [1, 2, 3];
        $ticketB->labels         = ['foo', 'bar'];
        $ticketB->new_messages[] = 'foo';
        $ticketB->custom_fields  = [
            new CustomData(1, [10]),
            new CustomData(2, [21]),
            new CustomData(4, strtotime('2018-01-01 01:00:00')),
            new CustomData(5, 0),
        ];

        return [$ticketA, $ticketB];
    }

    public function testChangedFields()
    {
        list($ticketA, $ticketB) = $this->getTickets();

        $ticketChange = new TicketChange($ticketA, $ticketB);

        $this->assertEquals(
            [
                'ticket.data.2', 'ticket.data.3', 'ticket.data.4', 'ticket.data.5',
                'ticket.id', 'ticket.followers', 'ticket.labels', 'ticket.is_new', 'ticket.new_messages',
            ],
            $ticketChange->getChangedFields(),
            '', 0.0, 10, true
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
