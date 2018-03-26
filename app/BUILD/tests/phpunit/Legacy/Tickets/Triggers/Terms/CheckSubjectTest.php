<?php

namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

require_once 'AbstractTicketStringCheckTest.php';

class CheckSubjectTest extends AbstractTicketStringCheckTest
{
    /**
     * {@inheritdoc}
     */
    protected function getCheckClass()
    {
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckSubject';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCheckClassOptionKey()
    {
        return 'subject';
    }

    /**
     * {@inheritdoc}
     */
    public function getTicketPropertyName()
    {
        return 'subject';
    }
}
