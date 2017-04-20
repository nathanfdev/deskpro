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
