<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1629466470 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', "DELETE FROM notification_system_event");

        // unused but delete anyway
        $this->execDbQuery('default', "DELETE FROM permissions_cache");
    }
}
