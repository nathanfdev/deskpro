<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1541680188 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE app2_app ADD `created_at` DATETIME DEFAULT NULL, ADD `updated_at` DATETIME DEFAULT NULL, ADD `bundle_updated_at` DATETIME DEFAULT NULL');

        $d = '2018-11-08 12:29:48';
        $this->execDbQuery('default', "UPDATE app2_app SET created_at = '$d', updated_at = '$d', bundle_updated_at = '$d'");
    }

    public function run()
    {
    }
}
