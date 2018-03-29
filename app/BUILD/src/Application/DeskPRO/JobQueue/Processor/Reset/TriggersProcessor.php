<?php

namespace Application\DeskPRO\JobQueue\Processor\Reset;

use Application\DeskPRO\Monolog\NullLogger;
use Application\InstallBundle\Data\DefaultData\TriggerData;

class TriggersProcessor extends Base
{
    const JOB_TYPE = 'reset.triggers';

    /**
     * {@inheritdoc}
     */
    protected function doProcess(array $data)
    {
        $this->connection->executeUpdate('DELETE FROM ticket_triggers');
        $trigger_data = new TriggerData($this->container, new NullLogger());
        $trigger_data->runInstall();
    }
}
