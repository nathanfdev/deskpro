<?php

namespace Application\DeskPRO\JobQueue\Processor\Reset;

use Application\DeskPRO\Monolog\NullLogger;
use Application\InstallBundle\Data\DefaultData\FilterData;

class FiltersProcessor extends Base
{
    const JOB_TYPE = 'reset.filters';

    /**
     * {@inheritdoc}
     */
    protected function doProcess(array $data)
    {
        $this->connection->executeUpdate('DELETE FROM ticket_filters');
        $filter_data = new FilterData($this->container, new NullLogger());
        $filter_data->runReset();
    }
}
