<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1551368517 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $fk = $this->getSchemaHelper()->findForeignKey('ticket_charges', 'person_id', 'people', 'id');
        if ($fk) {
            $this->execDbQuery('default', "ALTER TABLE ticket_charges DROP FOREIGN KEY {$fk->getName()}");
        }
        $fk = $this->getSchemaHelper()->findForeignKey('ticket_charges', 'organization_id', 'organizations', 'id');
        if ($fk) {
            $this->execDbQuery('default', "ALTER TABLE ticket_charges DROP FOREIGN KEY {$fk->getName()}");
        }

        $this->execDbQuery('default', 'ALTER TABLE ticket_charges ADD CONSTRAINT FK_36230948217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE ticket_charges ADD CONSTRAINT FK_3623094832C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id) ON DELETE SET NULL');
    }

    public function run()
    {
    }
}
