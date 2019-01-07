<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1546868705 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQueryQuiet('default', 'CREATE TABLE plivo_endpoints (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, account_id INT DEFAULT NULL, endpoint_id VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, INDEX IDX_8491BDC8217BBB47 (person_id), INDEX IDX_8491BDC89B6B5FBA (account_id), UNIQUE INDEX person_endpoint (account_id, person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQueryQuiet('default', 'ALTER TABLE plivo_endpoints ADD CONSTRAINT FK_8491BDC8217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQueryQuiet('default', 'ALTER TABLE plivo_endpoints ADD CONSTRAINT FK_8491BDC89B6B5FBA FOREIGN KEY (account_id) REFERENCES voice_accounts (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
        $this->execDbQueryQuiet('default', 'DROP INDEX account_sid ON voice_accounts');
        $this->execDbQueryQuiet('default', 'ALTER TABLE voice_accounts ADD type VARCHAR(30) NOT NULL, ADD user_application_id VARCHAR(100) DEFAULT NULL, ADD agent_application_id VARCHAR(100) DEFAULT NULL, CHANGE account_sid account_id VARCHAR(100) NOT NULL');
        $this->execDbQueryQuiet('default', 'CREATE UNIQUE INDEX account_id ON voice_accounts (account_id, type)');
        $this->execDbQueryQuiet('default', 'DROP INDEX number_sid ON voice_numbers');
        $this->execDbQueryQuiet('default', 'ALTER TABLE voice_numbers DROP country_code');
        $this->execDbQueryQuiet('default', 'CREATE UNIQUE INDEX number_sid ON voice_numbers (account_id, sid)');
        $this->execDbQueryQuiet('default', 'ALTER TABLE voice_phone_calls ADD forwarding_request_ids LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\'');
    }

    public function run()
    {
    }
}
