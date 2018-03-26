<?php

namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckAgent;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckAgentTest extends AbstractTicketEntityCheckTest
{
    /**
     * {@inheritdoc}
     */
    protected function getCheckClass()
    {
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckAgent';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCheckClassOptionKey()
    {
        return 'agent_ids';
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return 'Application\\DeskPRO\\Entity\\Person';
    }

    /**
     * {@inheritdoc}
     */
    public function getTicketPropertyName()
    {
        return 'agent';
    }

    /**
     * {@inheritdoc}
     */
    public function createEntityObject($id)
    {
        $object = parent::createEntityObject($id);
        $object->setIsAgent(true);

        return $object;
    }

    public function testTouched()
    {
        $agent = new Person();
        $agent->setIsAgent(true);

        $ticket        = new Ticket();
        $ticket->agent = $agent;

        $context = new ExecutorContext();

        $this->assertTrue($ticket->getStateChangeRecorder()->hasTouchedField('agent'));

        $check = new CheckAgent('touched');
        $this->assertTrue($check->isTriggerMatch($ticket, $context));
    }

    public function testNotTouched()
    {
        $agent = new Person();
        $agent->setIsAgent(true);

        $ticket        = new Ticket();
        $ticket->agent = $agent;
        $ticket->resetStateChangeRecorder();

        $context = new ExecutorContext();

        $this->assertFalse($ticket->getStateChangeRecorder()->hasTouchedField('agent'));

        $check = new CheckAgent('nottouched');
        $this->assertTrue($check->isTriggerMatch($ticket, $context));
    }
}
