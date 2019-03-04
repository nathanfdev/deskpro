<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1551786313 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE voicemail_agent_recordings (id INT AUTO_INCREMENT NOT NULL, agent_id INT DEFAULT NULL, phone_call_id INT DEFAULT NULL, blob_id INT DEFAULT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', date_created DATETIME NOT NULL, is_listened TINYINT(1) NOT NULL, recording_url VARCHAR(255) NOT NULL, duration INT DEFAULT NULL, is_deleted TINYINT(1) NOT NULL, INDEX IDX_5C5E30A63414710B (agent_id), INDEX IDX_5C5E30A6C0EA171E (phone_call_id), UNIQUE INDEX UNIQ_5C5E30A6ED3E8EA5 (blob_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE voice_recordings (id INT AUTO_INCREMENT NOT NULL, phone_call_id INT DEFAULT NULL, blob_id INT DEFAULT NULL, recording_url VARCHAR(255) NOT NULL, duration INT DEFAULT NULL, is_deleted TINYINT(1) NOT NULL, INDEX IDX_B6A217CDC0EA171E (phone_call_id), UNIQUE INDEX UNIQ_B6A217CDED3E8EA5 (blob_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE voicemail_agent_recordings ADD CONSTRAINT FK_5C5E30A63414710B FOREIGN KEY (agent_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE voicemail_agent_recordings ADD CONSTRAINT FK_5C5E30A6C0EA171E FOREIGN KEY (phone_call_id) REFERENCES voice_phone_calls (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE voicemail_agent_recordings ADD CONSTRAINT FK_5C5E30A6ED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id)');
        $this->execDbQuery('default', 'ALTER TABLE voice_recordings ADD CONSTRAINT FK_B6A217CDC0EA171E FOREIGN KEY (phone_call_id) REFERENCES voice_phone_calls (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE voice_recordings ADD CONSTRAINT FK_B6A217CDED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id)');
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls DROP FOREIGN KEY FK_6679AE4C8CA9A845');
        $this->execDbQuery('default', 'DROP INDEX UNIQ_6679AE4C8CA9A845 ON voice_phone_calls');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls DROP recording_id, DROP recording_deleted, DROP duration');
    }

    public function run()
    {
    }
}
