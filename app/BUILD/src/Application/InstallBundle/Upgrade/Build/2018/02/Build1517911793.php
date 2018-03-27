<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1517911793 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE settings_brand MODIFY id INT NOT NULL');
        $this->execDbQuery('default', 'ALTER TABLE settings_brand DROP PRIMARY KEY');
        $this->execDbQuery('default', 'ALTER TABLE settings_brand DROP id');
        $this->execDbQuery('default', 'ALTER TABLE settings_brand ADD PRIMARY KEY (name, brand_id)');

        $this->execDbQuery('default', 'ALTER TABLE settings MODIFY id INT NOT NULL');
        $this->execDbQuery('default', 'ALTER TABLE settings DROP PRIMARY KEY');
        $this->execDbQuery('default', 'ALTER TABLE settings DROP id');
        $this->execDbQuery('default', 'ALTER TABLE settings ADD PRIMARY KEY (name)');
    }

    public function run()
    {
    }
}
