<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1606148285 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }


    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE article_comments ADD content_type VARCHAR(120) DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE download_comments ADD content_type VARCHAR(120) DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE topic_comments ADD content_type VARCHAR(120) DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_comments ADD content_type VARCHAR(120) DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE news_comments ADD content_type VARCHAR(120) DEFAULT NULL');
    }

    public function run()
    {
    }
}
