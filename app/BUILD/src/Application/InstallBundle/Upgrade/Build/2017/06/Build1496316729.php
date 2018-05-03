<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1496316729 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $badIndex = $this->getSchemaHelper()->findIndex('app2_app_state', ['app_instance_id', 'name']);
        if ($badIndex) {
            $this->out('Dropping invalid index on app2_app_state');
            $this->execDbQuery('default', "DROP INDEX `{$badIndex->getName()}` ON app2_app_state");
        }

        $goodIndex = $this->getSchemaHelper()->findIndex('app2_app_state', ['app_instance_id', 'name', 'owner_id']);
        if (!$goodIndex) {
            $this->out('Creating correct index on app2_app_state');
            $this->execDbQuery('default', 'CREATE UNIQUE INDEX state_unique ON app2_app_state (app_instance_id, name, owner_id)');
        }
    }

    public function run()
    {
    }
}
