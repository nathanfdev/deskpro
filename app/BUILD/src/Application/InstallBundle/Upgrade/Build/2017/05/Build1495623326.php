<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1495623326 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->out('Dropping old client_messages table');
        $this->execDbQuery('default', 'DROP TABLE IF EXISTS client_messages');
    }

    public function run()
    {
    }
}
