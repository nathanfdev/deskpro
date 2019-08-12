<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1565606454 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execSlowAlterTable('email_sources', 'ADD uuid VARCHAR(36) DEFAULT NULL AFTER uid, ADD UNIQUE INDEX UNIQ_6F9D0D3D539B0606 (uuid)');
        $this->execSlowAlterTable('sendmail_sources', 'ADD uuid VARCHAR(36) DEFAULT NULL AFTER ref, ADD UNIQUE INDEX UNIQ_9195FF45D17F50A6 (uuid)');
    }

    public function run()
    {
    }
}
