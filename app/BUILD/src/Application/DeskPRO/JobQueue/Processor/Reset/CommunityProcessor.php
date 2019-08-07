<?php

namespace Application\DeskPRO\JobQueue\Processor\Reset;

class CommunityProcessor extends Base
{
    const JOB_TYPE = 'reset.community';

    /**
     * {@inheritdoc}
     */
    protected function doProcess(array $data)
    {
        $this->connection->executeUpdate('DELETE FROM community_topics');
        $this->connection->executeUpdate('DELETE FROM custom_def_community_topic');
        $this->connection->executeUpdate('DELETE FROM community_channels');
        $this->connection->executeUpdate('DELETE FROM community_topic_status_categories');
    }
}
