<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1550595926 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'TRUNCATE TABLE sess_data');
        $this->execDbQuery('default', 'CREATE INDEX sess_time_idx ON sess_data (sess_time)');
    }

    public function run()
    {
    }
}
