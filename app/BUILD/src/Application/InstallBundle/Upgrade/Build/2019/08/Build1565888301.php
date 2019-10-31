<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1565888301 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls DROP FOREIGN KEY FK_6679AE4CC06401D9');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD CONSTRAINT FK_6679AE4CC06401D9 FOREIGN KEY (full_recording_id) REFERENCES voice_recordings (id) ON DELETE SET NULL');
    }

    public function run()
    {
    }
}
