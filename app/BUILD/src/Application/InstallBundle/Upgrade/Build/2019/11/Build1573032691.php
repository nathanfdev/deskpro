<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1573032691 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD account_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD CONSTRAINT FK_6679AE4C9B6B5FBA FOREIGN KEY (account_id) REFERENCES voice_accounts (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'CREATE INDEX IDX_6679AE4C9B6B5FBA ON voice_phone_calls (account_id)');
    }

    public function run()
    {
    }
}
