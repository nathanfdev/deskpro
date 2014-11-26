<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\EmailGateway\Reader\ValueReader;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;

require_once 'AbstractTicketStringCheckTest.php';

class CheckEmailCcNameTest extends AbstractStringCheckTest
{
    /**
     * @param  int    $id
     * @param  string $test_string
     * @return Ticket
     */
    public function createTicket($id, $test_string)
    {
        $ticket = new Ticket();
        $ticket->id = $id;
        $ticket->subject = $test_string;

        return $ticket;
    }

    /**
     * @param  Ticket          $ticket
     * @return ExecutorContext
     */
    public function createExecutorContext(Ticket $ticket)
    {
        $name = $ticket->subject;

        $value_reader = new ValueReader();
        $value_reader->setValues(array(
            'ccs' => array($name => 'test@example.com')
        ));

        $exec = new ExecutorContext();
        $exec->setEmailContext($value_reader);

        return $exec;
    }

    /**
     * {@inheritDoc}
     */
    protected function getCheckClass()
    {
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckEmailCcName';
    }

    /**
     * {@inheritDoc}
     */
    protected function getCheckClassOptionKey()
    {
        return 'name';
    }

    public function testNoEmailContext()
    {
        $ticket = new Ticket();
        $exec = new ExecutorContext();

        $check = $this->createChecker('is', array('%OPT%' => $this->getString1()));
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));
    }
}
