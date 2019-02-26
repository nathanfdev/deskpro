<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1551219602 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD outgoing_request_ids LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\'');
    }

    public function run()
    {
    }
}
