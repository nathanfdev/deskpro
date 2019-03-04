<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1551700388 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', 'INSERT INTO `settings` (`name`, `value`) VALUES (\'elastica.requires_reset\', 1) ON DUPLICATE KEY UPDATE `value` = 1;');
        $this->execDbQuery('default', 'DELETE FROM `datastore` WHERE `name` = \'sys.es_indexer\';');
    }
}
