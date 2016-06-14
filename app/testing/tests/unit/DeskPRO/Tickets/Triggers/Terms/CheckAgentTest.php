<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
