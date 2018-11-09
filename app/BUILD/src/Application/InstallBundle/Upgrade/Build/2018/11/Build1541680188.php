<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1541680188 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE app2_app ADD `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP, ADD `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP, ADD `bundle_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP');
    }

    public function run()
    {
    }
}
