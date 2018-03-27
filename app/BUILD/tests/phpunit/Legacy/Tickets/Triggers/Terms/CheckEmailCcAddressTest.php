<?php

namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\EmailGateway\Reader\ValueReader;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;

require_once 'AbstractTicketStringCheckTest.php';

class CheckEmailCcAddressTest extends AbstractStringCheckTest
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
     * @return string
     */
    public function getString1()
    {
        return 'example@example.com';
    }

    /**
     * @return string
     */
    public function getString2()
    {
        return 'example@example.com.other.tld';
    }

    /**
     * @param Ticket $ticket
     *
     * @return ExecutorContext
     */
    public function createExecutorContext(Ticket $ticket)
    {
        $email = $ticket->subject;

        $value_reader = new ValueReader();
        $value_reader->setValues([
            'ccs' => ['Name' => $email],
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
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckEmailCcAddress';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCheckClassOptionKey()
    {
        return 'email';
    }

    public function testNoEmailContext()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $check = $this->createChecker('is', ['%OPT%' => $this->getString1()]);
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));
    }
}
