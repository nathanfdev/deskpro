<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckPriorityTest extends AbstractTicketEntityCheckTest
{
    /**
     * {@inheritDoc}
     */
    protected function getCheckClass()
    {
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckPriority';
    }

    /**
     * {@inheritDoc}
     */
    protected function getCheckClassOptionKey()
    {
        return 'priority_ids';
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityClass()
    {
        return 'Application\\DeskPRO\\Entity\\TicketPriority';
    }

    /**
     * {@inheritDoc}
     */
    public function getTicketPropertyName()
    {
        return 'priority';
    }
}
