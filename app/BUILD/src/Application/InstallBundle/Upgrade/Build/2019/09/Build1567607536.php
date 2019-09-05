<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1567607536 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }


    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE approvals ADD created_by INT NOT NULL');
        $this->execDbQuery('default', 'ALTER TABLE approvals ADD CONSTRAINT FK_B7A4D6DEDE12AB56 FOREIGN KEY (created_by) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'CREATE INDEX IDX_B7A4D6DEDE12AB56 ON approvals (created_by)');
    }

    public function run()
    {
    }
}
