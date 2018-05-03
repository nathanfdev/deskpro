<?php

namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\SetStatus;
use Application\DeskPRO\Tickets\ExecutorContext;
use DpTest\DeskProTestCase;

class SetStatusTest extends DeskProTestCase
{
    public function testSet()
    {
        $ticket         = new Ticket();
        $ticket->status = 'awaiting_agent';
        $exec           = new ExecutorContext();

        $action = new SetStatus(['status' => 'awaiting_user']);

        $action->applyAction($ticket, $exec);

        $this->assertEquals('awaiting_user', $ticket->getStatusCode());
    }

    public function testSet2()
    {
        $ticket         = new Ticket();
        $ticket->status = 'awaiting_agent';
        $exec           = new ExecutorContext();

        $action = new SetStatus(['status' => 'hidden.deleted']);

        $action->applyAction($ticket, $exec);

        $this->assertEquals('hidden.deleted', $ticket->getStatusCode());
    }

    /**
     * @expectedException \Orb\Util\CheckedOptionsException
     */
    public function testInvalid()
    {
        $ticket         = new Ticket();
        $ticket->status = 'awaiting_agent';

        $exec = new ExecutorContext();

        $action = new SetStatus(['status' => 'asdadasdasdsad']);

        $this->assertTrue($action->isNoop($ticket, $exec));
    }

    public function testNoop2()
    {
        $ticket         = new Ticket();
        $ticket->status = 'awaiting_agent';

        $exec = new ExecutorContext();

        $action = new SetStatus(['status' => 'awaiting_agent']);

        $this->assertTrue($action->isNoop($ticket, $exec));
    }
}
