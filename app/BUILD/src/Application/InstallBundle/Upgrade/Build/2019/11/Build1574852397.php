<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1574852397 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', 'DELETE FROM settings WHERE name = "beta_features.messenger"');
    }
}
