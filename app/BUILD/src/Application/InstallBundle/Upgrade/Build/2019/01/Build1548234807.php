<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1548234807 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', '
            CREATE TABLE IF NOT EXISTS `lock_keys` (
              `key_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
              `key_token` varchar(44) COLLATE utf8_unicode_ci NOT NULL,
              `key_expiration` int(10) unsigned NOT NULL,
              PRIMARY KEY (`key_id`)
            ) ENGINE=InnoDB
        ');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
