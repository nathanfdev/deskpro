<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1560968706 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voicemail_agent_recordings RENAME TO voice_missed_agent_calls');
    }

    public function run()
    {
    }
}
