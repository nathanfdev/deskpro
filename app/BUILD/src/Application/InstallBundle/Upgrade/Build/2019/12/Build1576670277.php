<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1576670277 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }


    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE email_sources ADD client_ip VARCHAR(130) DEFAULT NULL, ADD client_host VARCHAR(255) DEFAULT NULL');
    }

    public function run()
    {
    }
}
