<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1565606460 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        // rename tables
        $this->execDbQuery('default', 'RENAME TABLE custom_data_feedback TO custom_data_community_topic');
        $this->execDbQuery('default', 'RENAME TABLE custom_def_feedback TO custom_def_community_topic');
        $this->execDbQuery('default', 'RENAME TABLE feedback TO community_topics');
        $this->execDbQuery('default', 'RENAME TABLE feedback_attachments TO community_topic_attachments');
        $this->execDbQuery('default', 'RENAME TABLE feedback_categories TO community_channels');
        $this->execDbQuery('default', 'RENAME TABLE feedback_category2usergroup TO community_channel2usergroup');
        $this->execDbQuery('default', 'RENAME TABLE feedback_comments TO community_topic_comments');
        $this->execDbQuery('default', 'RENAME TABLE feedback_revisions TO community_topic_revisions');
        $this->execDbQuery('default', 'RENAME TABLE feedback_slug_history TO community_topic_slug_history');
        $this->execDbQuery('default', 'RENAME TABLE feedback_status_categories TO community_topic_status_categories');
        $this->execDbQuery('default', 'RENAME TABLE feedback_subscriptions TO community_topic_subscriptions');
        $this->execDbQuery('default', 'RENAME TABLE labels_feedback TO labels_community_topics');
        $this->execDbQuery('default', 'RENAME TABLE ticket_feedback_links TO ticket_community_topics_links');

        // change columns
        $this->execDbQuery('default', 'ALTER TABLE community_topic_revisions CHANGE COLUMN feedback_id community_topic_id INT(11) NULL DEFAULT NULL AFTER id;');
        $this->execDbQuery('default', 'ALTER TABLE custom_data_community_topic ALTER feedback_id DROP DEFAULT;');
        $this->execDbQuery('default', 'ALTER TABLE custom_data_community_topic CHANGE COLUMN feedback_id topic_id INT(11) NOT NULL AFTER id;');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_subscriptions CHANGE COLUMN feedback_id topic_id INT(11) NULL DEFAULT NULL AFTER person_id;');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_slug_history ALTER feedback_id DROP DEFAULT;');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_slug_history CHANGE COLUMN feedback_id topic_id INT(11) NOT NULL AFTER id;');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_attachments CHANGE COLUMN feedback_id topic_id INT(11) NULL DEFAULT NULL AFTER id;');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_comments CHANGE COLUMN feedback_id topic_id INT(11) NULL DEFAULT NULL AFTER id;');
        $this->execDbQuery('default', 'ALTER TABLE labels_community_topics CHANGE COLUMN feedback_id topic_id INT(11) NOT NULL;');
        $this->execDbQuery('default', 'ALTER TABLE ticket_community_topics_links ALTER feedback_id DROP DEFAULT;');
        $this->execDbQuery('default', 'ALTER TABLE ticket_community_topics_links CHANGE COLUMN feedback_id topic_id INT(11) NOT NULL AFTER ticket_id;');
        $this->execDbQuery('default', 'ALTER TABLE community_channel2usergroup CHANGE COLUMN category_id community_channel_id INT(11) NOT NULL;');
        $this->execDbQuery('default', 'ALTER TABLE content_subscriptions CHANGE feedback_id topic_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE community_topics CHANGE category_id channel_id INT DEFAULT NULL');
    }

    public function run()
    {
        $this->execDbQuery('default', "UPDATE `ratings` SET `object_type` = 'community_topic' where `object_type` = 'feedback'");
        $this->execDbQuery('default', "UPDATE `label_defs` SET `label_type` = 'community' where `label_type` = 'feedback'");
        $this->execDbQuery('default', "UPDATE `content_search` SET `object_type` = 'community' where `object_type` = 'feedback'");
        $this->execDbQuery('default', "UPDATE `content_search_attribute` SET `object_type` = 'community', `attribute_id` = 'channel_id' where `object_type` = 'feedback'");
        $this->execDbQuery('default', "UPDATE `permissions` SET `name` = REPLACE(`name`, 'feedback.', 'community.') WHERE `name` LIKE \"%feedback.%\"");
        $this->execDbQuery('default', "UPDATE `settings` SET `name` = REPLACE(`name`, 'feedback', 'community') WHERE `name` LIKE \"%feedback%\"");
        $this->execDbQuery('default', "UPDATE `settings_brand` SET `name` = REPLACE(`name`, 'feedback', 'community') WHERE `name` LIKE \"%feedback%\"");
        $this->execDbQuery('default', "UPDATE `hit_record` SET `page_type` = 'deskpro.community_view' WHERE `page_type` = 'deskpro.feedback_view'");
        $this->execDbQuery('default', "UPDATE `people_prefs` SET `name` = REPLACE('newfeedback', 'newcommunitytopic', `name`) WHERE `name` LIKE \"%newfeedback%\"");
        $this->execDbQuery('default', "UPDATE `people_prefs` SET `name` = REPLACE('feedback', 'community', `name`) WHERE `name` LIKE \"%feedback%\"");
        $this->execDbQuery('default', "UPDATE `people_prefs` SET `name` = REPLACE('feedback', 'community', `name`) WHERE `name` LIKE \"%feedback%\"");
        $this->execDbQuery('default', "UPDATE `custom_def_community_topic` SET `sys_name` = 'chan' WHERE `sys_name` = 'cat'");
    }
}
