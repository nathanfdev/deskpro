<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1557215151 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_auto_attendants DROP FOREIGN KEY FK_94EFE731B75393B6');
        $this->execDbQuery('default', 'DROP INDEX UNIQ_94EFE731B75393B6 ON voice_auto_attendants');
        $this->execDbQuery('default', 'ALTER TABLE voice_auto_attendants CHANGE audioasset_id audio_asset_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_auto_attendants ADD CONSTRAINT FK_94EFE7318A8030B7 FOREIGN KEY (audio_asset_id) REFERENCES voice_assets (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX UNIQ_94EFE7318A8030B7 ON voice_auto_attendants (audio_asset_id)');
        $this->execDbQuery('default', 'ALTER TABLE voice_queues DROP FOREIGN KEY FK_80C86EA43642E22');
        $this->execDbQuery('default', 'ALTER TABLE voice_queues DROP FOREIGN KEY FK_80C86EA96094606');
        $this->execDbQuery('default', 'ALTER TABLE voice_queues DROP FOREIGN KEY FK_80C86EADC60BD4E');
        $this->execDbQuery('default', 'DROP INDEX UNIQ_80C86EA43642E22 ON voice_queues');
        $this->execDbQuery('default', 'DROP INDEX UNIQ_80C86EA96094606 ON voice_queues');
        $this->execDbQuery('default', 'DROP INDEX UNIQ_80C86EADC60BD4E ON voice_queues');
        $this->execDbQuery('default', 'ALTER TABLE voice_queues ADD greet_asset_id INT DEFAULT NULL, ADD loop_asset_id INT DEFAULT NULL, ADD voicemail_asset_id INT DEFAULT NULL, DROP greetAsset_id, DROP loopAsset_id, DROP voicemailAsset_id');
        $this->execDbQuery('default', 'ALTER TABLE voice_queues ADD CONSTRAINT FK_80C86EA41C0D9EE FOREIGN KEY (greet_asset_id) REFERENCES voice_assets (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_queues ADD CONSTRAINT FK_80C86EA398D79B7 FOREIGN KEY (loop_asset_id) REFERENCES voice_assets (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_queues ADD CONSTRAINT FK_80C86EA7D165057 FOREIGN KEY (voicemail_asset_id) REFERENCES voice_assets (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX UNIQ_80C86EA41C0D9EE ON voice_queues (greet_asset_id)');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX UNIQ_80C86EA398D79B7 ON voice_queues (loop_asset_id)');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX UNIQ_80C86EA7D165057 ON voice_queues (voicemail_asset_id)');
        $this->execDbQuery('default', 'ALTER TABLE agent_data DROP FOREIGN KEY FK_6849807D165057');
        $this->execDbQuery('default', 'ALTER TABLE agent_data ADD CONSTRAINT FK_6849807D165057 FOREIGN KEY (voicemail_asset_id) REFERENCES voice_assets (id) ON DELETE SET NULL');
    }

    public function run()
    {
    }
}
