<?php
namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\SetDeleted;
use Application\DeskPRO\Tickets\ExecutorContext;

class SetDeletedTest extends \DpUnitTestCase
{
    public function testDelete()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $action = new SetDeleted();
        $action->applyAction($ticket, $exec);

        $this->assertEquals('hidden.deleted', $ticket->getStatusCode());
    }

    public function testNoop()
    {
        $ticket = new Ticket();
        $ticket->status = 'hidden.deleted';
        $exec   = new ExecutorContext();

        $action = new SetDeleted();
        $this->assertTrue($action->isNoop($ticket, $exec));
    }
}
