<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1558077448 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', "UPDATE voice_queues SET routing_model = 'round_robin' WHERE routing_model = 'automatic'");
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
