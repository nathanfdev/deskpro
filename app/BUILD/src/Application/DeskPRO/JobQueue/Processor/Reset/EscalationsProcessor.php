<?php

namespace Application\DeskPRO\JobQueue\Processor\Reset;

class EscalationsProcessor extends Base
{
    const JOB_TYPE = 'reset.escalations';

    /**
     * {@inheritdoc}
     */
    protected function doProcess(array $data)
    {
        $this->connection->executeUpdate('DELETE FROM ticket_escalations');
        $this->connection->executeUpdate('DELETE FROM ticket_slas');
        $this->connection->executeUpdate('DELETE FROM slas');
    }
}
