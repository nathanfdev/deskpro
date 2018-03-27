<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1509706523 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE zapier_hooks ADD params LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', ADD person_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE zapier_hooks ADD CONSTRAINT FK_FAD644B4217BBB47 FOREIGN KEY (person_id) REFERENCES people (id)');
        $this->execDbQuery('default', 'CREATE INDEX IDX_FAD644B4217BBB47 ON zapier_hooks (person_id)');
    }

    public function run()
    {
    }
}
