<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1618333810 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        if (defined('DPC_IS_CLOUD')) {
            return;
        }

        $this->saveSetting('core.lic_limit_grace_until', strtotime('+21 days'));
    }
}
