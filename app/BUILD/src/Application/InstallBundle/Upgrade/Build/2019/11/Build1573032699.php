<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1573032699 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE community_forum_to_custom_def_community_topic (forum_id INT NOT NULL, field_id INT NOT NULL, display_order INT NOT NULL DEFAULT 0, INDEX IDX_998435E429CCBAD0 (forum_id), INDEX IDX_998435E4443707B0 (field_id), PRIMARY KEY(forum_id, field_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE community_forum_to_status (forum_id INT NOT NULL, status_id INT NOT NULL, display_order INT NOT NULL DEFAULT 0, INDEX IDX_D378160729CCBAD0 (forum_id), INDEX IDX_D37816076BF700BD (status_id), PRIMARY KEY(forum_id, status_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE community_forum_to_custom_def_community_topic ADD CONSTRAINT FK_998435E429CCBAD0 FOREIGN KEY (forum_id) REFERENCES community_forums (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE community_forum_to_custom_def_community_topic ADD CONSTRAINT FK_998435E4443707B0 FOREIGN KEY (field_id) REFERENCES custom_def_community_topic (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE community_forum_to_status ADD CONSTRAINT FK_D378160729CCBAD0 FOREIGN KEY (forum_id) REFERENCES community_forums (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE community_forum_to_status ADD CONSTRAINT FK_D37816076BF700BD FOREIGN KEY (status_id) REFERENCES community_topic_status_categories (id) ON DELETE CASCADE');
    }


    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE custom_def_community_topic ADD is_global TINYINT(1) NOT NULL DEFAULT 0');
        $this->execDbQuery('default', 'ALTER TABLE community_forums ADD splash_image_property_id INT DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL, ADD color VARCHAR(6) DEFAULT NULL, ADD is_voting_enabled TINYINT(1) NOT NULL DEFAULT 1');
        $this->execDbQuery('default', 'ALTER TABLE community_forums ADD CONSTRAINT FK_8F94AF4B339FD429 FOREIGN KEY (splash_image_property_id) REFERENCES splash_image_property (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX UNIQ_8F94AF4B665648E9 ON community_forums (color)');
        $this->execDbQuery('default', 'CREATE INDEX IDX_8F94AF4B339FD429 ON community_forums (splash_image_property_id)');
    }

    public function run()
    {
        $this->execDbQuery('default', 'INSERT INTO community_forum_to_status (forum_id, status_id) SELECT f.id, c.id FROM community_forums f CROSS JOIN community_topic_status_categories c');
        $this->execDbQuery('default', "DELETE FROM custom_def_community_topic WHERE id IN (SELECT ID FROM (SELECT DISTINCT tf.id FROM custom_def_community_topic tf LEFT JOIN custom_data_community_topic td ON tf.id = td.field_id WHERE tf.sys_name = 'cat' AND td.id IS NULL) AS t)");
        $this->execDbQuery('default', "UPDATE custom_def_community_topic SET is_global = 1 WHERE id IN (SELECT ID FROM (SELECT DISTINCT tf.id FROM custom_def_community_topic tf INNER JOIN custom_data_community_topic td ON tf.id = td.field_id WHERE tf.sys_name = 'cat') AS t)");
    }
}
