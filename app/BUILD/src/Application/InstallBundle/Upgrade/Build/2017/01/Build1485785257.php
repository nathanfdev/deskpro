<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1485785257 extends AbstractBuild
{
    public function run()
    {
        $this->out('Add agent_data.outbound_calls_enabled');
        $this->execDbQuery('default', 'ALTER TABLE agent_data ADD outbound_calls_enabled TINYINT(1) NOT NULL');
    }
}
