<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1598448476 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        // changes to Person::serialize and Person::unserialize
        // mean existing portal sessions have to be reset
        $this->truncateTable('default', 'sessions');
    }
}
