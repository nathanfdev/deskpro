<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1518431273 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        // clean default dash without a name
        $this->execDbQuery('default', 'DELETE FROM report_dashboard WHERE is_default = 1 AND system_name IS NULL');
    }

    public function run()
    {
    }
}
