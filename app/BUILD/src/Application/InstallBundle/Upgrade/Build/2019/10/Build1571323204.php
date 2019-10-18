<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1571323204 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'CREATE INDEX storage_loc_pref_idx ON blobs (storage_loc_pref)');
    }

    public function run()
    {
    }
}
