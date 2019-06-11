<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1560243638 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE agent_data ADD forwarding_ring_timeout INT DEFAULT NULL');
    }

    public function run()
    {
    }
}
