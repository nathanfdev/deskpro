<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1536774805 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_numbers CHANGE outbound_calls_default_global outbound_calls_default_type VARCHAR(255) NOT NULL');
    }

    public function run()
    {
    }
}
