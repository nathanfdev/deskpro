<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1559155631 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE articles ADD date_next_review DATETIME DEFAULT NULL, ADD review_interval VARCHAR(50) DEFAULT NULL');
    }

    public function run()
    {
    }
}
