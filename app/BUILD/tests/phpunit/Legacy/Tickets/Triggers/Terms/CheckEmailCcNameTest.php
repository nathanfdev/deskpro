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

use Application\DeskPRO\EmailGateway\Reader\ValueReader;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;

require_once 'AbstractTicketStringCheckTest.php';

class CheckEmailCcNameTest extends AbstractStringCheckTest
{
    /**
     * @param int    $id
     * @param string $test_string
     *
     * @return Ticket
     */
    public function createTicket($id, $test_string)
    {
        $ticket          = new Ticket();
        $ticket->id      = $id;
        $ticket->subject = $test_string;

        return $ticket;
    }

    /**
     * @param Ticket $ticket
     *
     * @return ExecutorContext
     */
    public function createExecutorContext(Ticket $ticket)
    {
        $name = $ticket->subject;

        $value_reader = new ValueReader();
        $value_reader->setValues([
            'ccs' => [$name => 'test@example.com'],
        ]);

        $exec = new ExecutorContext();
        $exec->setEmailContext($value_reader);

        return $exec;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCheckClass()
    {
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckEmailCcName';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCheckClassOptionKey()
    {
        return 'name';
    }

    public function testNoEmailContext()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $check = $this->createChecker('is', ['%OPT%' => $this->getString1()]);
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));
    }
}
