<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1564500272 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execSlowAlterTable('email_sources', 'ADD recipients LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\'');
    }

    public function run()
    {
    }
}
