<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1571653599 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }


    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE approvals DROP FOREIGN KEY FK_B7A4D6DEDE12AB56');
        $this->execDbQuery('default', 'ALTER TABLE approvals ADD CONSTRAINT FK_B7A4D6DEDE12AB56 FOREIGN KEY (created_by) REFERENCES people (id) ON DELETE SET NULL');
    }

    public function run()
    {
    }
}
