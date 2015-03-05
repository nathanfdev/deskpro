<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckUserVar;

require_once 'AbstractTicketStringCheckTest.php';

class CheckUserVarTest extends AbstractStringCheckTest
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

    public function createExecutorContext(Ticket $ticket)
    {
        $exec = new ExecutorContext();
        $exec->getUserVars()->set("testvar", $ticket->subject);

        return $exec;
    }

    /**
     * {@inheritDoc}
     */
    protected function createChecker($op, array $options)
    {
        if (isset($options['%OPT%'])) {
            $opt_key = $this->getCheckClassOptionKey();
            $options[$opt_key] = $options['%OPT%'];
            unset($options['%OPT%']);
        }

        $options['name'] = 'testvar';

        $check = new CheckUserVar($op, $options);

        return $check;
    }

    /**
     * {@inheritDoc}
     */
    protected function getCheckClass()
    {
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckUserVar';
    }

    /**
     * {@inheritDoc}
     */
    protected function getCheckClassOptionKey()
    {
        return 'value';
    }
}
