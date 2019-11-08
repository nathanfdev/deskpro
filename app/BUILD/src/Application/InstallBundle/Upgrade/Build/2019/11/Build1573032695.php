<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1573032695 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execSlowAlterTable('community_topic_status_categories', 'ADD color VARCHAR(6) DEFAULT NULL');
        $this->execSlowAlterTable('news_categories', 'ADD color VARCHAR(6) DEFAULT NULL');
    }
}
