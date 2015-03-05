<?php
namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\ModSetEmailValidation;
use Application\DeskPRO\Tickets\ExecutorContext;

class ModSetEmailValidationTest extends \DpUnitTestCase
{
    public function testEnable()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $action = new ModSetEmailValidation(array('enable_validation' => true));
        $action->applyAction($ticket, $exec);

        $this->assertTrue($exec->getVars()->get('enable_email_validation'));
    }

    public function testDisable()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $action = new ModSetEmailValidation(array('enable_validation' => false));
        $action->applyAction($ticket, $exec);

        $this->assertFalse($exec->getVars()->get('enable_email_validation'));
    }
}
