<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1489058013 extends AbstractBuild
{
    public function run()
    {
        $this->out('Inverse agent_data reference');

        $instructions = [];

        $instructions[] = 'ADD agent_data_id INT DEFAULT NULL';
        $instructions[] = 'ADD CONSTRAINT FK_28166A26E5A6C396 FOREIGN KEY (agent_data_id) REFERENCES agent_data (id) ON DELETE SET NULL';
        $instructions[] = 'ADD UNIQUE INDEX UNIQ_28166A26E5A6C396 (agent_data_id)';

        $this->execSlowAlterTable('people', implode(', ', $instructions));

        $fk = $this->getSchemaHelper()->findForeignKey('agent_data', 'person_id', 'people', 'id');
        if ($fk) {
            $this->out("-- Drop existing FK {$fk->getName()}");
            $this->execDbQuery('default', "ALTER TABLE agent_data DROP FOREIGN KEY {$fk->getName()}");
        }

        $key = $this->getSchemaHelper()->findIndex('agent_data', 'person_id');
        if ($key) {
            $this->execDbQuery('default', "DROP INDEX {$key->getName()} ON agent_data");
        }
        $this->execDbQuery('default', 'ALTER TABLE agent_data DROP person_id');
    }
}
