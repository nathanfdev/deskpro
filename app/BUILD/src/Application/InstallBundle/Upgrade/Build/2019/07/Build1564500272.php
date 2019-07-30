<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1564500272 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE email_sources ADD recipients LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\'');
    }

    public function run()
    {
    }
}
