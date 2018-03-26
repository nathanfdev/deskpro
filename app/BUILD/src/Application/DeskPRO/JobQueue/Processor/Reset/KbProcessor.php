<?php

namespace Application\DeskPRO\JobQueue\Processor\Reset;

class KbProcessor extends Base
{
    const JOB_TYPE = 'reset.kb';

    /**
     * {@inheritdoc}
     */
    protected function doProcess(array $data)
    {
        $this->connection->executeUpdate('DELETE FROM articles');
    }
}
