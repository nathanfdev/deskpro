<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1535472949 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        //that's it, delete before alter
        $this->execDbQuery('default', 'DELETE FROM api_key_log');
        $this->execDbQuery('default', 'DELETE FROM api_log');

        $this->execDbQuery('default', 'CREATE INDEX time_idx ON api_key_log (`time`)');
        $this->execDbQuery('default', 'CREATE INDEX start_time_idx ON api_log (start_time)');
        $this->execDbQuery('default', 'CREATE INDEX end_time_idx ON api_log (end_time)');
    }

    public function run()
    {
    }
}
