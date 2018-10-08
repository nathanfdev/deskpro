<?php

namespace Application\InstallBundle\Upgrade\Build;

use DeskPRO\Component\Util\RandUtils;

class Build1538993220 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        if (!$this->readSetting('core.helpdesk_uuid')) {
            $this->saveSetting('core.helpdesk_uuid', RandUtils::uuidV4());
        }
    }
}
