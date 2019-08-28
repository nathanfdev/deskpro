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
        $this->getSchemaHelper()->renameColumn('community_topic_revisions', 'feedback_id', 'topic_id');
        $this->getSchemaHelper()->renameColumn('custom_data_community_topic', 'feedback_id', 'topic_id');
        $this->getSchemaHelper()->renameColumn('community_topic_subscriptions', 'feedback_id', 'topic_id');
        $this->getSchemaHelper()->renameColumn('community_topic_slug_history', 'feedback_id', 'topic_id');
        $this->getSchemaHelper()->renameColumn('community_topic_attachments', 'feedback_id', 'topic_id');
        $this->getSchemaHelper()->renameColumn('community_topic_comments', 'feedback_id', 'topic_id');
        $this->getSchemaHelper()->renameColumn('ticket_community_topics_links', 'feedback_id', 'topic_id');
        $this->getSchemaHelper()->renameColumn('community_channel2usergroup', 'category_id', 'community_channel_id');
        $this->getSchemaHelper()->renameColumn('content_subscriptions', 'feedback_id', 'topic_id');
        $this->getSchemaHelper()->renameColumn('community_topics', 'category_id', 'channel_id');
    }

    public function run()
    {
        $this->execDbQuery('default', "UPDATE IGNORE `ratings` SET `object_type` = 'community_topic' where `object_type` = 'feedback'");
        $this->execDbQuery('default', "UPDATE IGNORE `label_defs` SET `label_type` = 'community' where `label_type` = 'feedback'");
        $this->execDbQuery('default', "UPDATE IGNORE `content_search` SET `object_type` = 'community' where `object_type` = 'feedback'");
        $this->execDbQuery('default', "UPDATE IGNORE `content_search_attribute` SET `object_type` = 'community', `attribute_id` = 'channel_id' where `object_type` = 'feedback'");
        $this->execDbQuery('default', "UPDATE IGNORE `permissions` SET `name` = REPLACE(`name`, 'feedback.', 'community.') WHERE `name` LIKE \"%feedback.%\"");
        $this->execDbQuery('default', "UPDATE IGNORE `settings` SET `name` = REPLACE(`name`, 'feedback', 'community') WHERE `name` LIKE \"%feedback%\"");
        $this->execDbQuery('default', "UPDATE IGNORE `settings_brand` SET `name` = REPLACE(`name`, 'feedback', 'community') WHERE `name` LIKE \"%feedback%\"");
        $this->execDbQuery('default', "UPDATE IGNORE `hit_record` SET `page_type` = 'deskpro.community_view' WHERE `page_type` = 'deskpro.feedback_view'");
        $this->execDbQuery('default', "UPDATE IGNORE `people_prefs` SET `name` = REPLACE('newfeedback', 'newcommunitytopic', `name`) WHERE `name` LIKE \"%newfeedback%\"");
        $this->execDbQuery('default', "UPDATE IGNORE `people_prefs` SET `name` = REPLACE('feedback', 'community', `name`) WHERE `name` LIKE \"%feedback%\"");
        $this->execDbQuery('default', "UPDATE IGNORE `people_prefs` SET `name` = REPLACE('feedback', 'community', `name`) WHERE `name` LIKE \"%feedback%\"");
        $this->execDbQuery('default', "UPDATE IGNORE `custom_def_community_topic` SET `sys_name` = 'chan' WHERE `sys_name` = 'cat'");
    }
}
