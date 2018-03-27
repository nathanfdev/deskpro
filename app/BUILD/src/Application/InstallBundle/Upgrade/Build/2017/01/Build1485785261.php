<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1485785261 extends AbstractBuild
{
    public function run()
    {
        $this->out('Alter to voice_phone_calls');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD recording_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD CONSTRAINT FK_6679AE4C8CA9A845 FOREIGN KEY (recording_id) REFERENCES blobs (id)');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX UNIQ_6679AE4C8CA9A845 ON voice_phone_calls (recording_id)');
    }
}
