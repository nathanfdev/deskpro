<?php

namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckPriorityTest extends AbstractTicketEntityCheckTest
{
    /**
     * {@inheritdoc}
     */
    protected function getCheckClass()
    {
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckPriority';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCheckClassOptionKey()
    {
        return 'priority_ids';
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return 'Application\\DeskPRO\\Entity\\TicketPriority';
    }

    /**
     * {@inheritdoc}
     */
    public function getTicketPropertyName()
    {
        return 'priority';
    }
}
