<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckTestFalse;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckTestTrue;
use Application\DeskPRO\Tickets\Triggers\Terms\TriggerTermComposite;

require_once 'AbstractTicketEntityCheckTest.php';

class TriggerTermCompositeTest extends \DpUnitTestCase
{
    public function testAndFirstFailure()
    {
        $ticket = new Ticket();
        $exec = new ExecutorContext();

        $terms = array();
        $terms[] = new CheckTestFalse('is');
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestTrue('is');

        $check = new TriggerTermComposite($terms, 'and');
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));
    }

    public function testAndLastFailure()
    {
        $ticket = new Ticket();
        $exec = new ExecutorContext();

        $terms = array();
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestFalse('is');

        $check = new TriggerTermComposite($terms, 'and');
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));
    }

    public function testAndAllFailure()
    {
        $ticket = new Ticket();
        $exec = new ExecutorContext();

        $terms = array();
        $terms[] = new CheckTestFalse('is');
        $terms[] = new CheckTestFalse('is');
        $terms[] = new CheckTestFalse('is');

        $check = new TriggerTermComposite($terms, 'and');
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));
    }

    public function testAndAllPass()
    {
        $ticket = new Ticket();
        $exec = new ExecutorContext();

        $terms = array();
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestTrue('is');

        $check = new TriggerTermComposite($terms, 'and');
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));
    }

    public function testOrFirstFailure()
    {
        $ticket = new Ticket();
        $exec = new ExecutorContext();

        $terms = array();
        $terms[] = new CheckTestFalse('is');
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestTrue('is');

        $check = new TriggerTermComposite($terms, 'or');
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));
    }

    public function testOrLastFailure()
    {
        $ticket = new Ticket();
        $exec = new ExecutorContext();

        $terms = array();
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestFalse('is');

        $check = new TriggerTermComposite($terms, 'or');
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));
    }

    public function testOrAllFailure()
    {
        $ticket = new Ticket();
        $exec = new ExecutorContext();

        $terms = array();
        $terms[] = new CheckTestFalse('is');
        $terms[] = new CheckTestFalse('is');
        $terms[] = new CheckTestFalse('is');

        $check = new TriggerTermComposite($terms, 'or');
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));
    }

    public function testOrAllPass()
    {
        $ticket = new Ticket();
        $exec = new ExecutorContext();

        $terms = array();
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestTrue('is');

        $check = new TriggerTermComposite($terms, 'or');
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));
    }

    public function testNested()
    {
        $ticket = new Ticket();
        $exec = new ExecutorContext();

        $terms = array();
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestTrue('is');
        $check1 = new TriggerTermComposite($terms, 'and');

        $terms = array();
        $terms[] = new CheckTestFalse('is');
        $terms[] = new CheckTestFalse('is');
        $terms[] = new CheckTestTrue('is');
        $check2 = new TriggerTermComposite($terms, 'or');

        $check = new TriggerTermComposite(array($check1, $check2), 'and');
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));
    }
}
