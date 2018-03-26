<?php

namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\ModStopTriggers;
use Application\DeskPRO\Tickets\ExecutorContext;
use DpTest\DeskProTestCase;

class ModStopTriggersTest extends DeskProTestCase
{
    public function testAdd()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $action = new ModStopTriggers();
        $action->applyAction($ticket, $exec);

        $this->assertTrue($exec->getVars()->get('stop_triggers'));
    }
}
