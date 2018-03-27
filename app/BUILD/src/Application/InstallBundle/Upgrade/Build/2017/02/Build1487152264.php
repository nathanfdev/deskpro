<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1487152264 extends AbstractBuild
{
    public function run()
    {
        $this->out('New voice table');
        $this->execDbQuery('default', "CREATE TABLE voicemail_records (id INT AUTO_INCREMENT NOT NULL, phone_call_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, data LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', date_created DATETIME NOT NULL, is_deleted TINYINT(1) NOT NULL, is_listened TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_571DEC0EC0EA171E (phone_call_id), INDEX IDX_571DEC0E3414710B (agent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->execDbQuery('default', 'ALTER TABLE voicemail_records ADD CONSTRAINT FK_571DEC0EC0EA171E FOREIGN KEY (phone_call_id) REFERENCES voice_phone_calls (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE voicemail_records ADD CONSTRAINT FK_571DEC0E3414710B FOREIGN KEY (agent_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE voice_assets DROP INDEX UNIQ_BE17F9F2ED3E8EA5, ADD INDEX IDX_BE17F9F2ED3E8EA5 (blob_id)');
        $this->execDbQuery('default', 'ALTER TABLE voice_assets ADD auth VARCHAR(20) NOT NULL, ADD date_created DATETIME NOT NULL, CHANGE name name VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(30) NOT NULL, CHANGE text text LONGTEXT DEFAULT NULL, CHANGE language language LONGTEXT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD duration INT DEFAULT NULL');
    }
}
