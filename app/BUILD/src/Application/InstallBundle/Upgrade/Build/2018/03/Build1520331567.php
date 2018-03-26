<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1520331567 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE ticket_webhooks CHANGE payload_decoder payload_decoder VARCHAR(255) DEFAULT NULL');
    }

    public function run()
    {
    }
}
