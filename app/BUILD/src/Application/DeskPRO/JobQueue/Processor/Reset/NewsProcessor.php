<?php

namespace Application\DeskPRO\JobQueue\Processor\Reset;

class NewsProcessor extends Base
{
    const JOB_TYPE = 'reset.news';

    /**
     * {@inheritdoc}
     */
    protected function doProcess(array $data)
    {
        $this->connection->executeUpdate('DELETE FROM news');
    }
}
