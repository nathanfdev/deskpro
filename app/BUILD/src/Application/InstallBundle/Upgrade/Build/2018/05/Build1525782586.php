<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1525782586 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->out('Setting flag to keep old reports available');
        $this->saveSetting('core.show_old_reports', true);

        $this->out('Enable new reports');
        $this->saveSetting('beta_features.new_reports', 1);
    }
}
