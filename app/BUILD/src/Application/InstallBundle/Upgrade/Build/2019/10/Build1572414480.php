<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1572414480 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE approvals DROP FOREIGN KEY FK_B7A4D6DEDE12AB56');
        $this->execDbQuery('default', 'DROP INDEX IDX_B7A4D6DEDE12AB56 ON approvals');
        $this->execDbQuery('default', 'ALTER TABLE approvals DROP created_by');
    }

    public function run()
    {
    }
}
