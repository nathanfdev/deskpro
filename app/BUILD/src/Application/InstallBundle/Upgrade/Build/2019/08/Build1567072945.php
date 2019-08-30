<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1567072945 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE approval_approvers (approval_id BIGINT NOT NULL, person_id INT NOT NULL, INDEX IDX_99119303FE65F000 (approval_id), INDEX IDX_99119303217BBB47 (person_id), PRIMARY KEY(approval_id, person_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE approval_approvers ADD CONSTRAINT FK_99119303FE65F000 FOREIGN KEY (approval_id) REFERENCES approvals (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE approval_approvers ADD CONSTRAINT FK_99119303217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE approvals DROP approvers');
    }

    public function run()
    {
    }
}
