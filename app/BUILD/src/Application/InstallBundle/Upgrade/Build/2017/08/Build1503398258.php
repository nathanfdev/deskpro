<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1503398258 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQueryQuiet('default', "UPDATE app2_app SET name = 'deskpro-app-trello' WHERE name = 'deskproapps-trello'");
    }

    public function run()
    {
    }
}
