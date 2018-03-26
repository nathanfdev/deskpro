<?php

namespace Application\DeskPRO\JobQueue\Processor\Reset;

class OrganizationsProcessor extends Base
{
    const JOB_TYPE = 'reset.organizations';

    /**
     * {@inheritdoc}
     */
    protected function doProcess(array $data)
    {
        $this->connection->executeUpdate('DELETE FROM organizations');
        $this->connection->executeUpdate('DELETE FROM organizations_deleted');
    }
}
