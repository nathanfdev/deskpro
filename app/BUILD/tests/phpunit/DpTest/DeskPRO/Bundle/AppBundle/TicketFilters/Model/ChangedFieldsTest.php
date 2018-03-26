<?php

namespace DpTest\Bundle\AppBundle\TicketFilters\Model;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;

class ChangedFieldsTest extends \PHPUnit_Framework_TestCase
{
    public function testTicketChanges()
    {
        $ticketA            = new TicketModel();
        $ticketA->id        = 1;
        $ticketA->followers = [1, 2];
        $ticketA->labels    = ['foo'];

        $ticketB            = new TicketModel();
        $ticketB->id        = 2;
        $ticketA->followers = [1, 2, 3];
        $ticketA->labels    = ['foo', 'bar'];

        $diff = $ticketA->getChangedFields($ticketB);

        $this->assertEquals(
            ['ticket.id', 'ticket.followers', 'ticket.labels'],
            $diff
        );
    }
}
