<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1606900297 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE article_comments ADD parent_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE article_comments ADD CONSTRAINT FK_A766241727ACA70 FOREIGN KEY (parent_id) REFERENCES article_comments (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_A766241727ACA70 ON article_comments (parent_id)');
        $this->execDbQuery('default', 'ALTER TABLE download_comments ADD parent_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE download_comments ADD CONSTRAINT FK_B43CDE14727ACA70 FOREIGN KEY (parent_id) REFERENCES download_comments (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_B43CDE14727ACA70 ON download_comments (parent_id)');
        $this->execDbQuery('default', 'ALTER TABLE topic_comments ADD parent_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE topic_comments ADD CONSTRAINT FK_A9AF1B2C727ACA70 FOREIGN KEY (parent_id) REFERENCES topic_comments (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_A9AF1B2C727ACA70 ON topic_comments (parent_id)');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_comments ADD parent_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_comments ADD CONSTRAINT FK_9ACCE956727ACA70 FOREIGN KEY (parent_id) REFERENCES community_topic_comments (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_9ACCE956727ACA70 ON community_topic_comments (parent_id)');
        $this->execDbQuery('default', 'ALTER TABLE news_comments ADD parent_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE news_comments ADD CONSTRAINT FK_16A0357B727ACA70 FOREIGN KEY (parent_id) REFERENCES news_comments (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_16A0357B727ACA70 ON news_comments (parent_id)');
    }

    public function run()
    {
    }
}
