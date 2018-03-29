<?php

namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\ModSetUserVar;
use Application\DeskPRO\Tickets\ExecutorContext;
use DpTest\DeskProTestCase;

class ModSetUserVarTest extends DeskProTestCase
{
    public function testSet()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $action = new ModSetUserVar(['name' => 'testvar', 'value' => 'test123']);
        $action->applyAction($ticket, $exec);

        $this->assertTrue($exec->getUserVars()->has('testvar'));
        $this->assertEquals('test123', $exec->getUserVars()->get('testvar'));
    }

    public function testNotSet()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $action = new ModSetUserVar(['name' => 'xxx', 'value' => 'test123']);
        $action->applyAction($ticket, $exec);

        $this->assertFalse($exec->getUserVars()->has('testvar'));
    }
}
