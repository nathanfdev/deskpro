<?php

namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckWorkflowTest extends AbstractTicketEntityCheckTest
{
    /**
     * {@inheritdoc}
     */
    protected function getCheckClass()
    {
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckWorkflow';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCheckClassOptionKey()
    {
        return 'workflow_ids';
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return 'Application\\DeskPRO\\Entity\\TicketWorkflow';
    }

    /**
     * {@inheritdoc}
     */
    public function getTicketPropertyName()
    {
        return 'workflow';
    }
}
