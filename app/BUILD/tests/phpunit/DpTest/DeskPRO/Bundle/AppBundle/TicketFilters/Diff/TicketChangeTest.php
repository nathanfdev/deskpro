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
