<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1485785260 extends AbstractBuild
{
    public function run()
    {
        $this->out('Alter to voice_numbers');
        $this->execDbQuery('default', 'ALTER TABLE voice_numbers CHANGE nickname nickname VARCHAR(255) DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_numbers ADD outbound_calls_enabled TINYINT(1) NOT NULL;');
    }
}
