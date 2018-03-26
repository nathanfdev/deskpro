<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1491407808 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE topic_subscriptions (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, topic_id INT DEFAULT NULL, root_category TINYINT(1) DEFAULT NULL, INDEX IDX_F34BD8DB217BBB47 (person_id), INDEX IDX_F34BD8DB1F55203D (topic_id), INDEX root_category_idx (root_category), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE topic_subscriptions ADD CONSTRAINT FK_F34BD8DB217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE topic_subscriptions ADD CONSTRAINT FK_F34BD8DB1F55203D FOREIGN KEY (topic_id) REFERENCES topics (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', 'DELETE FROM settings WHERE name = \'user.portal_tabs_order\'');
    }
}
