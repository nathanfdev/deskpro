<?php

namespace Application\DeskPRO\JobQueue\Processor\Reset;

class FeedbackProcessor extends Base
{
    const JOB_TYPE = 'reset.feedback';

    /**
     * {@inheritdoc}
     */
    protected function doProcess(array $data)
    {
        $this->connection->executeUpdate('DELETE FROM feedback');
        $this->connection->executeUpdate('DELETE FROM custom_def_feedback');
        $this->connection->executeUpdate('DELETE FROM feedback_categories');
        $this->connection->executeUpdate('DELETE FROM feedback_status_categories');
    }
}
