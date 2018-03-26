<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1518022975 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', "
            INSERT IGNORE INTO permissions SELECT NULL, usergroup_id, person_id, 'agent_publish.use', 1, 1
            FROM permissions
            GROUP BY usergroup_id, person_id
        ");
    }
}
