<?php

namespace Application\DeskPRO\JobQueue\Processor\Reset;

class LabelsProcessor extends Base
{
    const JOB_TYPE = 'reset.labels';

    /**
     * {@inheritdoc}
     */
    protected function doProcess(array $data)
    {
        $types = [
            'articles', 'blobs', 'chat_conversations', 'downloads', 'feedback', 'news', 'organizations',
            'people', 'tasks', 'tickets',
        ];

        foreach ($types as $type) {
            $this->connection->executeUpdate("DELETE FROM labels_$type");
        }
        $this->connection->executeUpdate('DELETE FROM label_defs');
    }
}
