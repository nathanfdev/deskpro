<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1536229552 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->out('Clearing key logs');
        $this->truncateTable('default', 'api_key_log');
        $this->truncateTable('default', 'api_log');

        $this->out('Adding indexes on key log tables');
        $this->execDbQuery('default', 'CREATE INDEX time_idx ON api_key_log (`time`)');
        $this->execDbQuery('default', 'CREATE INDEX start_time_idx ON api_log (start_time)');
        $this->execDbQuery('default', 'CREATE INDEX end_time_idx ON api_log (end_time)');
    }

    public function run()
    {
    }
}
