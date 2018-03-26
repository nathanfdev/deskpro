<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1507539183 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', 'DELETE FROM app_instances WHERE package_name = \'deskpro_sendgrid\'');
        $this->execDbQuery('default', 'DELETE FROM app_packages WHERE name = \'deskpro_sendgrid\'');
    }
}
