<?php

namespace Application\DeskPRO\JobQueue\Processor\Reset;

class SnippetsProcessor extends Base
{
    const JOB_TYPE = 'reset.snippets';

    /**
     * {@inheritdoc}
     */
    protected function doProcess(array $data)
    {
        $this->connection->executeUpdate('DELETE FROM ticket_macros');
        $this->connection->executeUpdate('DELETE FROM text_snippet_categories');
        $this->connection->executeUpdate("DELETE FROM object_lang WHERE ref_type IN ('text_snippets', 'text_snippet_categories')");
    }
}
