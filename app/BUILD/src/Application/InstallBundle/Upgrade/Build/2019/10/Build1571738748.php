<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1571738748 extends AbstractBuild implements OnlineBuildInterface
{

    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE community_topic_status_transitions (id INT AUTO_INCREMENT NOT NULL, topic_id INT NOT NULL, old_status_category_id INT DEFAULT NULL, new_status_category_id INT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_DED43A321F55203D (topic_id), INDEX IDX_DED43A326FFB00B6 (old_status_category_id), INDEX IDX_DED43A328D940871 (new_status_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_status_transitions ADD CONSTRAINT FK_DED43A321F55203D FOREIGN KEY (topic_id) REFERENCES community_topics (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_status_transitions ADD CONSTRAINT FK_DED43A326FFB00B6 FOREIGN KEY (old_status_category_id) REFERENCES community_topic_status_categories (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_status_transitions ADD CONSTRAINT FK_DED43A328D940871 FOREIGN KEY (new_status_category_id) REFERENCES community_topic_status_categories (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', 'INSERT INTO community_topic_status_transitions SELECT NULL, id, NULL, status_category_id, COALESCE(date_updated, date_published, date_created) FROM community_topics WHERE status_category_id IS NOT NULL');
    }
}
