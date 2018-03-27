<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1487846338 extends AbstractBuild
{
    public function run()
    {
        $this->out('Alter voice_phone_calls');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls CHANGE call_sid call_sid VARCHAR(50) DEFAULT NULL, CHANGE from_number external_number VARCHAR(50) NOT NULL');
    }
}
