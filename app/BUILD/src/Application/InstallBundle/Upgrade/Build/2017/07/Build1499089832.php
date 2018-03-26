<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1499089832 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $fk = $this->getSchemaHelper()->findForeignKey('voice_numbers', 'target_id', 'voice_targets', 'id');
        if ($fk) {
            $this->out("-- Drop existing FK {$fk->getName()}");
            $this->execDbQuery('default', "ALTER TABLE voice_numbers DROP FOREIGN KEY {$fk->getName()}");
        }

        $this->execDbQuery('default', 'ALTER TABLE voice_numbers ADD CONSTRAINT FK_2EEA316A158E0B66 FOREIGN KEY (target_id) REFERENCES voice_targets (id) ON DELETE SET NULL');
    }

    public function run()
    {
    }
}
