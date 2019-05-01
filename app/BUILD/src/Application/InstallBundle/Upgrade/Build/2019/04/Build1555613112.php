<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1555613112 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD call_sids LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', DROP forwarding_sids, DROP forwarding_request_ids, DROP outgoing_request_ids');
    }

    public function run()
    {
    }
}
