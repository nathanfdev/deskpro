<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1565606459 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD full_recording_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD CONSTRAINT FK_6679AE4CC06401D9 FOREIGN KEY (full_recording_id) REFERENCES voice_recordings (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX UNIQ_6679AE4CC06401D9 ON voice_phone_calls (full_recording_id)');
        $this->execDbQuery('default', 'ALTER TABLE voice_recordings ADD metadata LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\'');
    }

    public function run()
    {
    }
}
