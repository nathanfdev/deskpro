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

namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckTestFalse;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckTestTrue;
use Application\DeskPRO\Tickets\Triggers\Terms\TriggerTermComposite;
use DpTest\DeskProTestCase;

require_once 'AbstractTicketEntityCheckTest.php';

class TriggerTermCompositeTest extends DeskProTestCase
{
    public function testAndFirstFailure()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $terms   = [];
        $terms[] = new CheckTestFalse('is');
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestTrue('is');

        $check = new TriggerTermComposite($terms, 'and');
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));
    }

    public function testAndLastFailure()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $terms   = [];
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestFalse('is');

        $check = new TriggerTermComposite($terms, 'and');
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));
    }

    public function testAndAllFailure()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $terms   = [];
        $terms[] = new CheckTestFalse('is');
        $terms[] = new CheckTestFalse('is');
        $terms[] = new CheckTestFalse('is');

        $check = new TriggerTermComposite($terms, 'and');
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));
    }

    public function testAndAllPass()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $terms   = [];
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestTrue('is');

        $check = new TriggerTermComposite($terms, 'and');
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));
    }

    public function testOrFirstFailure()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $terms   = [];
        $terms[] = new CheckTestFalse('is');
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestTrue('is');

        $check = new TriggerTermComposite($terms, 'or');
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));
    }

    public function testOrLastFailure()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $terms   = [];
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestFalse('is');

        $check = new TriggerTermComposite($terms, 'or');
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));
    }

    public function testOrAllFailure()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $terms   = [];
        $terms[] = new CheckTestFalse('is');
        $terms[] = new CheckTestFalse('is');
        $terms[] = new CheckTestFalse('is');

        $check = new TriggerTermComposite($terms, 'or');
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));
    }

    public function testOrAllPass()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $terms   = [];
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestTrue('is');

        $check = new TriggerTermComposite($terms, 'or');
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));
    }

    public function testNested()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $terms   = [];
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestTrue('is');
        $terms[] = new CheckTestTrue('is');
        $check1  = new TriggerTermComposite($terms, 'and');

        $terms   = [];
        $terms[] = new CheckTestFalse('is');
        $terms[] = new CheckTestFalse('is');
        $terms[] = new CheckTestTrue('is');
        $check2  = new TriggerTermComposite($terms, 'or');

        $check = new TriggerTermComposite([$check1, $check2], 'and');
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));
    }
}
