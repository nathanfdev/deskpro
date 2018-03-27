<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1487152261 extends AbstractBuild
{
    public function run()
    {
        $this->out('Voice table update');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD type VARCHAR(50) NOT NULL');
    }
}
