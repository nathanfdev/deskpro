<?php

namespace Application\DeskPRO\JobQueue\Processor\Reset;

class DownloadsProcessor extends Base
{
    const JOB_TYPE = 'reset.downloads';

    /**
     * {@inheritdoc}
     */
    protected function doProcess(array $data)
    {
        $this->connection->executeUpdate('DELETE FROM downloads');
    }
}
