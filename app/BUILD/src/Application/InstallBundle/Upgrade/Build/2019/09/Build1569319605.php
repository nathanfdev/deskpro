<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1569319605 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', "UPDATE voice_phone_calls SET `status` = 'ended' WHERE `date_created` < (CURRENT_DATE() - INTERVAL 1 DAY) AND status IN ('pending', 'warm_add', 'warm_transfer', 'cold_transfer', 'active')");
    }
}
