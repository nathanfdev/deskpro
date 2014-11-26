<?php
namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\ModMuteUserEmails;
use Application\DeskPRO\Tickets\ExecutorContext;

class ModMuteUserEmailsTest extends \DpUnitTestCase
{
    public function testAdd()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $action = new ModMuteUserEmails();
        $action->applyAction($ticket, $exec);

        $this->assertTrue($exec->getVars()->get('mute_user_emails'));
    }
}
