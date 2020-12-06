<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1607275237 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE article_comments ADD rating INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE download_comments ADD rating INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE topic_comments ADD rating INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE news_comments ADD rating INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_comments ADD rating INT DEFAULT NULL');
    }

    public function run()
    {
    }
}
