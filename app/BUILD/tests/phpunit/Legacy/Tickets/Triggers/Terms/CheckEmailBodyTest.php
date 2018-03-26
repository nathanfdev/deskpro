<?php

namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\EmailGateway\Reader\ValueReader;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;

require_once 'AbstractTicketStringCheckTest.php';

class CheckEmailBodyTest extends AbstractStringCheckTest
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

    public function createExecutorContext(Ticket $ticket)
    {
        $value_reader = new ValueReader();

        if ($ticket->id % 2 === 0) {
            $value_reader->setValues([
                'body_html' => '<div><strong>'.$ticket->subject.'</strong>',
            ]);
        } else {
            $value_reader->setValues([
                'body_text' => $ticket->subject,
            ]);
        }

        $exec = new ExecutorContext();
        $exec->setEmailContext($value_reader);

        return $exec;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCheckClass()
    {
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckEmailBody';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCheckClassOptionKey()
    {
        return 'body';
    }

    public function testNoEmailContext()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $check = $this->createChecker('is', ['%OPT%' => $this->getString1()]);
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));
    }
}
