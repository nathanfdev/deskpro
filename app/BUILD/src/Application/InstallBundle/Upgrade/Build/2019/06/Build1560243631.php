<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1560243631 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls DROP FOREIGN KEY FK_6679AE4C2E24EDAB');
        $this->execDbQuery('default', 'DROP INDEX IDX_6679AE4C2E24EDAB ON voice_phone_calls');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls DROP voice_queue_id');
    }

    public function run()
    {
    }
}
