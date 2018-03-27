<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1482413243 extends AbstractBuild
{
    public function run()
    {
        $this->out('My Upgrade Class');
        $this->execDbQuery('default', "CREATE TABLE voice_phone_call_logs (id INT AUTO_INCREMENT NOT NULL, phone_call_id INT DEFAULT NULL, person_id INT DEFAULT NULL, action_type VARCHAR(100) NOT NULL, details LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', date_created DATETIME NOT NULL, INDEX IDX_ACB69564C0EA171E (phone_call_id), INDEX IDX_ACB69564217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_call_logs ADD CONSTRAINT FK_ACB69564C0EA171E FOREIGN KEY (phone_call_id) REFERENCES voice_phone_calls (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_call_logs ADD CONSTRAINT FK_ACB69564217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_call_participants ADD date_created DATETIME NOT NULL, ADD date_joined DATETIME DEFAULT NULL, ADD date_left DATETIME DEFAULT NULL, ADD type VARCHAR(30) NOT NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD person_id INT DEFAULT NULL, ADD date_created DATETIME NOT NULL, ADD date_started DATETIME DEFAULT NULL, ADD date_ended DATETIME DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD CONSTRAINT FK_6679AE4C217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'CREATE INDEX IDX_6679AE4C217BBB47 ON voice_phone_calls (person_id)');
    }
}
