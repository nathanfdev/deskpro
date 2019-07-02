<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1560443473 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voicemail_agent_recordings ADD transcription LONGTEXT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_recordings ADD transcription LONGTEXT DEFAULT NULL');
    }

    public function run()
    {
    }
}
