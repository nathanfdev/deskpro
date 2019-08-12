<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1565605219 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls DROP FOREIGN KEY FK_6679AE4C30A1DE10');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD CONSTRAINT FK_6679AE4C30A1DE10 FOREIGN KEY (number_id) REFERENCES voice_numbers (id) ON DELETE SET NULL');
    }

    public function run()
    {
    }
}
