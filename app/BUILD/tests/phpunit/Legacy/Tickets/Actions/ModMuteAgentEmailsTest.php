<?php

namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\ModMuteAgentEmails;
use Application\DeskPRO\Tickets\ExecutorContext;
use DpTest\DeskProTestCase;

class ModMuteAgentEmailsTest extends DeskProTestCase
{
    public function testAdd()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $action = new ModMuteAgentEmails();
        $action->applyAction($ticket, $exec);

        $this->assertTrue($exec->getVars()->get('mute_agent_emails'));
    }
}
