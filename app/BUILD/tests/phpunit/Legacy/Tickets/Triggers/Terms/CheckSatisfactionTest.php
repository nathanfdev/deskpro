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
use Application\DeskPRO\Tickets\Triggers\Terms\CheckSatisfaction;
use DpTest\DeskProTestCase;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckSatisfactionTest extends DeskProTestCase
{
    public function testPositive()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $ticket->date_feedback_rating = new \DateTime();
        $ticket->feedback_rating      = 1;

        $check = new CheckSatisfaction('is', ['rating' => 1]);
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));
    }

    public function testNegative()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $ticket->date_feedback_rating = new \DateTime();
        $ticket->feedback_rating      = -1;

        $check = new CheckSatisfaction('is', ['rating' => -1]);
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));
    }

    public function testNeutral()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $ticket->date_feedback_rating = new \DateTime();
        $ticket->feedback_rating      = 0;

        $check = new CheckSatisfaction('is', ['rating' => 0]);
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));
    }

    public function testIsset()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $ticket->date_feedback_rating = new \DateTime();
        $ticket->feedback_rating      = 0;

        $check = new CheckSatisfaction('isset');
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));
    }

    public function testNotIsset()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $check = new CheckSatisfaction('not_isset');
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));
    }
}
