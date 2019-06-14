<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1560496499 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voicemail_agent_recordings ADD recording_sid VARCHAR(50) DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_recordings ADD recording_sid VARCHAR(50) DEFAULT NULL');
        $this->execDbQuery('default', 'CREATE INDEX recording_sid ON voicemail_agent_recordings (recording_sid)');
        $this->execDbQuery('default', 'CREATE INDEX recording_sid ON voice_recordings (recording_sid)');
    }

    public function run()
    {
    }
}
