<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1590506913 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{

    public function addNewTables()
    {
    }


    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE ticket_statuses ADD options LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\'');
    }

    public function run()
    {
    }
}
