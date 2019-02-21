<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1550750781 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE ticket_charges DROP FOREIGN KEY FK_36230948217BBB47');
        $this->execDbQuery('default', 'ALTER TABLE ticket_charges DROP FOREIGN KEY FK_3623094832C8A3DE');
        $this->execDbQuery('default', 'ALTER TABLE ticket_charges ADD CONSTRAINT FK_36230948217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE ticket_charges ADD CONSTRAINT FK_3623094832C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id) ON DELETE SET NULL');
    }

    public function run()
    {
    }
}
