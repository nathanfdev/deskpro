<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1520331568 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'DROP INDEX unique_alias ON object_aliases');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX unique_alias ON object_aliases (alias)');
    }

    public function run()
    {
        $this->execDbQuery('default', "UPDATE object_aliases SET alias = CONCAT_WS(':', 'app', app_instance_id, alias) WHERE app_instance_id IS NOT NULL ");
    }
}
