<?php

namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\EmailGateway\Reader\ValueReader;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckEmailHeader;

require_once 'AbstractTicketStringCheckTest.php';

class CheckEmailHeaderTest extends AbstractStringCheckTest
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
        $value_reader = new ValueReader();

        $value_reader->setValues([
            'headers' => ['other-test' => ['xyz'], 'test-header' => ['abc', $ticket->subject]],
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
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckEmailHeader';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCheckClassOptionKey()
    {
        return 'value';
    }

    /**
     * @param string $op
     * @param array  $options
     *
     * @return CheckEmailHeader
     */
    protected function createChecker($op, array $options)
    {
        if (isset($options['%OPT%'])) {
            $opt_key           = $this->getCheckClassOptionKey();
            $options[$opt_key] = $options['%OPT%'];
            unset($options['%OPT%']);
        }

        $options['name'] = 'test-header';
        $check           = new CheckEmailHeader($op, $options);

        return $check;
    }

    public function testNoEmailContext()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $check = $this->createChecker('is', ['%OPT%' => $this->getString1()]);
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));
    }
}
