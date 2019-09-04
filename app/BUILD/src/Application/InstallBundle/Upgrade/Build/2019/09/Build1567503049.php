<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1567503049 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }


    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE approvals ADD cancelled_by INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE approvals ADD CONSTRAINT FK_B7A4D6DE48CCCEFB FOREIGN KEY (cancelled_by) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'CREATE INDEX IDX_B7A4D6DE48CCCEFB ON approvals (cancelled_by)');
    }

    public function run()
    {
    }
}
