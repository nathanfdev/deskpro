<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1559908999 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE agent_data ADD forwarding_logged_out TINYINT(1) NOT NULL');
    }

    public function run()
    {
    }
}
