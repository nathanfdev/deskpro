<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1562064076 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
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
        $this->execDbQuery('default', 'RENAME TABLE feedback_category2usergroup TO community_channels2usergroup');
        $this->execDbQuery('default', 'RENAME TABLE feedback_comments TO community_topic_comments');
        $this->execDbQuery('default', 'RENAME TABLE feedback_revisions TO community_topic_revisions');
        $this->execDbQuery('default', 'RENAME TABLE feedback_slug_history TO community_topic_slug_history');
        $this->execDbQuery('default', 'RENAME TABLE feedback_status_categories TO community_topic_status_categories');
        $this->execDbQuery('default', 'RENAME TABLE feedback_subscriptions TO community_topic_subscriptions');
        $this->execDbQuery('default', 'RENAME TABLE labels_feedback TO labels_community_topics');
        $this->execDbQuery('default', 'RENAME TABLE ticket_feedback_links TO ticket_community_topics_links');

        // change columns
        $this->execDbQuery('default', 'ALTER TABLE community_topic_revisions CHANGE COLUMN feedback_id topic_id INT(11) NULL DEFAULT NULL AFTER id;');
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
        $this->execDbQuery('default', 'ALTER TABLE community_channels2usergroup CHANGE COLUMN category_id community_channel_id INT(11) NOT NULL;');

        // update indexes

        $this->execDbQuery('default', 'ALTER TABLE community_topics DROP FOREIGN KEY FK_D229445812469DE2');
        $this->execDbQuery('default', 'DROP INDEX IDX_E03CB3CA12469DE2 ON community_topics');
        $this->execDbQuery('default', 'ALTER TABLE community_topics CHANGE category_id channel_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE community_topics ADD CONSTRAINT FK_E03CB3CA72F5A1AA FOREIGN KEY (channel_id) REFERENCES community_channels (id)');
        $this->execDbQuery('default', 'CREATE INDEX IDX_E03CB3CA72F5A1AA ON community_topics (channel_id)');
        $this->execDbQuery('default', 'ALTER TABLE community_channels DROP FOREIGN KEY FK_66FE683244F5D008');
        $this->execDbQuery('default', 'ALTER TABLE community_channels DROP FOREIGN KEY FK_66FE6832727ACA70');
        $this->execDbQuery('default', 'DROP INDEX uniq_66fe6832989d9b62 ON community_channels');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX UNIQ_1522791989D9B62 ON community_channels (slug)');
        $this->execDbQuery('default', 'DROP INDEX idx_66fe6832727aca70 ON community_channels');
        $this->execDbQuery('default', 'CREATE INDEX IDX_1522791727ACA70 ON community_channels (parent_id)');
        $this->execDbQuery('default', 'DROP INDEX idx_66fe683244f5d008 ON community_channels');
        $this->execDbQuery('default', 'CREATE INDEX IDX_152279144F5D008 ON community_channels (brand_id)');
        $this->execDbQuery('default', 'ALTER TABLE community_channels ADD CONSTRAINT FK_66FE683244F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE community_channels ADD CONSTRAINT FK_66FE6832727ACA70 FOREIGN KEY (parent_id) REFERENCES community_channels (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE community_channels2usergroup DROP FOREIGN KEY FK_B304B93C12469DE2');
        $this->execDbQuery('default', 'ALTER TABLE community_channels2usergroup DROP FOREIGN KEY FK_B304B93CD2112630');
        $this->execDbQuery('default', 'DROP INDEX idx_b304b93c12469de2 ON community_channels2usergroup');
        $this->execDbQuery('default', 'CREATE INDEX IDX_5A6CA578433ED7B6 ON community_channels2usergroup (community_channel_id)');
        $this->execDbQuery('default', 'DROP INDEX idx_b304b93cd2112630 ON community_channels2usergroup');
        $this->execDbQuery('default', 'CREATE INDEX IDX_5A6CA578D2112630 ON community_channels2usergroup (usergroup_id)');
        $this->execDbQuery('default', 'ALTER TABLE community_channels2usergroup ADD CONSTRAINT FK_B304B93C12469DE2 FOREIGN KEY (community_channel_id) REFERENCES community_channels (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE community_channels2usergroup ADD CONSTRAINT FK_B304B93CD2112630 FOREIGN KEY (usergroup_id) REFERENCES usergroups (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_comments DROP FOREIGN KEY FK_10D03D58217BBB47');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_comments DROP FOREIGN KEY FK_10D03D58D249A887');
        $this->execDbQuery('default', 'DROP INDEX idx_10d03d58d249a887 ON community_topic_comments');
        $this->execDbQuery('default', 'CREATE INDEX IDX_9ACCE9561F55203D ON community_topic_comments (topic_id)');
        $this->execDbQuery('default', 'DROP INDEX idx_10d03d58217bbb47 ON community_topic_comments');
        $this->execDbQuery('default', 'CREATE INDEX IDX_9ACCE956217BBB47 ON community_topic_comments (person_id)');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_comments ADD CONSTRAINT FK_10D03D58217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_comments ADD CONSTRAINT FK_10D03D58D249A887 FOREIGN KEY (topic_id) REFERENCES community_topics (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE email_uids CHANGE id id VARCHAR(100) NOT NULL');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_subscriptions DROP FOREIGN KEY FK_10EA54AA217BBB47');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_subscriptions DROP FOREIGN KEY FK_10EA54AAD249A887');
        $this->execDbQuery('default', 'DROP INDEX idx_10ea54aa217bbb47 ON community_topic_subscriptions');
        $this->execDbQuery('default', 'CREATE INDEX IDX_D2BCEED0217BBB47 ON community_topic_subscriptions (person_id)');
        $this->execDbQuery('default', 'DROP INDEX idx_10ea54aad249a887 ON community_topic_subscriptions');
        $this->execDbQuery('default', 'CREATE INDEX IDX_D2BCEED01F55203D ON community_topic_subscriptions (topic_id)');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_subscriptions ADD CONSTRAINT FK_10EA54AA217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_subscriptions ADD CONSTRAINT FK_10EA54AAD249A887 FOREIGN KEY (topic_id) REFERENCES community_topics (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_status_categories DROP FOREIGN KEY FK_F8E804FA44F5D008');
        $this->execDbQuery('default', 'DROP INDEX idx_f8e804fa44f5d008 ON community_topic_status_categories');
        $this->execDbQuery('default', 'CREATE INDEX IDX_AD28668A44F5D008 ON community_topic_status_categories (brand_id)');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_status_categories ADD CONSTRAINT FK_F8E804FA44F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_attachments DROP FOREIGN KEY FK_CC264F12217BBB47');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_attachments DROP FOREIGN KEY FK_CC264F12D249A887');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_attachments DROP FOREIGN KEY FK_CC264F12ED3E8EA5');
        $this->execDbQuery('default', 'DROP INDEX idx_cc264f12d249a887 ON community_topic_attachments');
        $this->execDbQuery('default', 'CREATE INDEX IDX_37ED3EC61F55203D ON community_topic_attachments (topic_id)');
        $this->execDbQuery('default', 'DROP INDEX idx_cc264f12217bbb47 ON community_topic_attachments');
        $this->execDbQuery('default', 'CREATE INDEX IDX_37ED3EC6217BBB47 ON community_topic_attachments (person_id)');
        $this->execDbQuery('default', 'DROP INDEX idx_cc264f12ed3e8ea5 ON community_topic_attachments');
        $this->execDbQuery('default', 'CREATE INDEX IDX_37ED3EC6ED3E8EA5 ON community_topic_attachments (blob_id)');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_attachments ADD CONSTRAINT FK_CC264F12217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_attachments ADD CONSTRAINT FK_CC264F12D249A887 FOREIGN KEY (topic_id) REFERENCES community_topics (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_attachments ADD CONSTRAINT FK_CC264F12ED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE community_topics DROP FOREIGN KEY FK_D229445812469DE2');
        $this->execDbQuery('default', 'ALTER TABLE community_topics DROP FOREIGN KEY FK_D2294458169CE813');
        $this->execDbQuery('default', 'ALTER TABLE community_topics DROP FOREIGN KEY FK_D2294458217BBB47');
        $this->execDbQuery('default', 'ALTER TABLE community_topics DROP FOREIGN KEY FK_D229445844F5D008');
        $this->execDbQuery('default', 'ALTER TABLE community_topics DROP FOREIGN KEY FK_D229445882F1BAF4');
        $this->execDbQuery('default', 'DROP INDEX uniq_d2294458989d9b62 ON community_topics');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX UNIQ_E03CB3CA989D9B62 ON community_topics (slug)');
        $this->execDbQuery('default', 'DROP INDEX idx_d2294458169ce813 ON community_topics');
        $this->execDbQuery('default', 'CREATE INDEX IDX_E03CB3CA169CE813 ON community_topics (status_category_id)');
        $this->execDbQuery('default', 'DROP INDEX idx_d229445812469de2 ON community_topics');
        $this->execDbQuery('default', 'CREATE INDEX IDX_E03CB3CA12469DE2 ON community_topics (category_id)');
        $this->execDbQuery('default', 'DROP INDEX idx_d2294458217bbb47 ON community_topics');
        $this->execDbQuery('default', 'CREATE INDEX IDX_E03CB3CA217BBB47 ON community_topics (person_id)');
        $this->execDbQuery('default', 'DROP INDEX idx_d229445882f1baf4 ON community_topics');
        $this->execDbQuery('default', 'CREATE INDEX IDX_E03CB3CA82F1BAF4 ON community_topics (language_id)');
        $this->execDbQuery('default', 'DROP INDEX idx_d229445844f5d008 ON community_topics');
        $this->execDbQuery('default', 'CREATE INDEX IDX_E03CB3CA44F5D008 ON community_topics (brand_id)');
        $this->execDbQuery('default', 'ALTER TABLE community_topics ADD CONSTRAINT FK_D229445812469DE2 FOREIGN KEY (category_id) REFERENCES community_channels (id)');
        $this->execDbQuery('default', 'ALTER TABLE community_topics ADD CONSTRAINT FK_D2294458169CE813 FOREIGN KEY (status_category_id) REFERENCES community_topic_status_categories (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE community_topics ADD CONSTRAINT FK_D2294458217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE community_topics ADD CONSTRAINT FK_D229445844F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE community_topics ADD CONSTRAINT FK_D229445882F1BAF4 FOREIGN KEY (language_id) REFERENCES languages (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_revisions DROP FOREIGN KEY FK_37F57C3E217BBB47');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_revisions DROP FOREIGN KEY FK_37F57C3ED249A887');
        $this->execDbQuery('default', 'DROP INDEX idx_37f57c3ed249a887 ON community_topic_revisions');
        $this->execDbQuery('default', 'CREATE INDEX IDX_D0C74DED1F55203D ON community_topic_revisions (topic_id)');
        $this->execDbQuery('default', 'DROP INDEX idx_37f57c3e217bbb47 ON community_topic_revisions');
        $this->execDbQuery('default', 'CREATE INDEX IDX_D0C74DED217BBB47 ON community_topic_revisions (person_id)');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_revisions ADD CONSTRAINT FK_37F57C3E217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_revisions ADD CONSTRAINT FK_37F57C3ED249A887 FOREIGN KEY (topic_id) REFERENCES community_topics (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE custom_def_community_topic DROP FOREIGN KEY FK_CC9CDDD844F5D008');
        $this->execDbQuery('default', 'ALTER TABLE custom_def_community_topic DROP FOREIGN KEY FK_CC9CDDD8727ACA70');
        $this->execDbQuery('default', 'ALTER TABLE custom_def_community_topic DROP FOREIGN KEY FK_CC9CDDD87987212D');
        $this->execDbQuery('default', 'DROP INDEX idx_cc9cddd8727aca70 ON custom_def_community_topic');
        $this->execDbQuery('default', 'CREATE INDEX IDX_3CAEAEEE727ACA70 ON custom_def_community_topic (parent_id)');
        $this->execDbQuery('default', 'DROP INDEX idx_cc9cddd87987212d ON custom_def_community_topic');
        $this->execDbQuery('default', 'CREATE INDEX IDX_3CAEAEEE7987212D ON custom_def_community_topic (app_id)');
        $this->execDbQuery('default', 'DROP INDEX idx_cc9cddd844f5d008 ON custom_def_community_topic');
        $this->execDbQuery('default', 'CREATE INDEX IDX_3CAEAEEE44F5D008 ON custom_def_community_topic (brand_id)');
        $this->execDbQuery('default', 'ALTER TABLE custom_def_community_topic ADD CONSTRAINT FK_CC9CDDD844F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE custom_def_community_topic ADD CONSTRAINT FK_CC9CDDD8727ACA70 FOREIGN KEY (parent_id) REFERENCES custom_def_community_topic (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE custom_def_community_topic ADD CONSTRAINT FK_CC9CDDD87987212D FOREIGN KEY (app_id) REFERENCES app_instances (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_slug_history DROP FOREIGN KEY FK_F0FF9966D249A887');
        $this->execDbQuery('default', 'DROP INDEX uniq_f0ff9966989d9b62 ON community_topic_slug_history');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX UNIQ_71BA44DA989D9B62 ON community_topic_slug_history (slug)');
        $this->execDbQuery('default', 'DROP INDEX idx_f0ff9966d249a887 ON community_topic_slug_history');
        $this->execDbQuery('default', 'CREATE INDEX IDX_71BA44DA1F55203D ON community_topic_slug_history (topic_id)');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_slug_history ADD CONSTRAINT FK_F0FF9966D249A887 FOREIGN KEY (topic_id) REFERENCES community_topics (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE custom_data_community_topic DROP FOREIGN KEY FK_92E9C37FD249A887');
        $this->execDbQuery('default', 'DROP INDEX IDX_92E9C37FD249A887 ON custom_data_community_topic');
        $this->execDbQuery('default', 'ALTER TABLE custom_data_community_topic DROP FOREIGN KEY FK_92E9C37F3F6A6D56');
        $this->execDbQuery('default', 'ALTER TABLE custom_data_community_topic DROP FOREIGN KEY FK_92E9C37F443707B0');
        $this->execDbQuery('default', 'ALTER TABLE custom_data_community_topic CHANGE topic_id feedback_id INT NOT NULL');
        $this->execDbQuery('default', 'ALTER TABLE custom_data_community_topic ADD CONSTRAINT FK_9D9E37CBD249A887 FOREIGN KEY (feedback_id) REFERENCES community_topics (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'CREATE INDEX IDX_9D9E37CBD249A887 ON custom_data_community_topic (feedback_id)');
        $this->execDbQuery('default', 'DROP INDEX idx_92e9c37f443707b0 ON custom_data_community_topic');
        $this->execDbQuery('default', 'CREATE INDEX IDX_9D9E37CB443707B0 ON custom_data_community_topic (field_id)');
        $this->execDbQuery('default', 'DROP INDEX idx_92e9c37f3f6a6d56 ON custom_data_community_topic');
        $this->execDbQuery('default', 'CREATE INDEX IDX_9D9E37CB3F6A6D56 ON custom_data_community_topic (root_field_id)');
        $this->execDbQuery('default', 'ALTER TABLE custom_data_community_topic ADD CONSTRAINT FK_92E9C37F3F6A6D56 FOREIGN KEY (root_field_id) REFERENCES custom_def_community_topic (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE custom_data_community_topic ADD CONSTRAINT FK_92E9C37F443707B0 FOREIGN KEY (field_id) REFERENCES custom_def_community_topic (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE labels_community_topics DROP FOREIGN KEY FK_42C4DA40D249A887');
        $this->execDbQuery('default', 'DROP INDEX idx_42c4da40d249a887 ON labels_community_topics');
        $this->execDbQuery('default', 'CREATE INDEX IDX_8B49BB791F55203D ON labels_community_topics (topic_id)');
        $this->execDbQuery('default', 'ALTER TABLE labels_community_topics ADD CONSTRAINT FK_42C4DA40D249A887 FOREIGN KEY (topic_id) REFERENCES community_topics (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE ticket_community_topics_links DROP FOREIGN KEY FK_BCDB23BE217BBB47');
        $this->execDbQuery('default', 'ALTER TABLE ticket_community_topics_links DROP FOREIGN KEY FK_BCDB23BE700047D2');
        $this->execDbQuery('default', 'ALTER TABLE ticket_community_topics_links DROP FOREIGN KEY FK_BCDB23BED249A887');
        $this->execDbQuery('default', 'DROP INDEX idx_bcdb23be700047d2 ON ticket_community_topics_links');
        $this->execDbQuery('default', 'CREATE INDEX IDX_82A4B493700047D2 ON ticket_community_topics_links (ticket_id)');
        $this->execDbQuery('default', 'DROP INDEX idx_bcdb23bed249a887 ON ticket_community_topics_links');
        $this->execDbQuery('default', 'CREATE INDEX IDX_82A4B4931F55203D ON ticket_community_topics_links (topic_id)');
        $this->execDbQuery('default', 'DROP INDEX idx_bcdb23be217bbb47 ON ticket_community_topics_links');
        $this->execDbQuery('default', 'CREATE INDEX IDX_82A4B493217BBB47 ON ticket_community_topics_links (person_id)');
        $this->execDbQuery('default', 'DROP INDEX ticket_feedback_links_unique ON ticket_community_topics_links');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX ticket_community_topics_links_unique ON ticket_community_topics_links (ticket_id, topic_id)');
        $this->execDbQuery('default', 'ALTER TABLE ticket_community_topics_links ADD CONSTRAINT FK_BCDB23BE217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE ticket_community_topics_links ADD CONSTRAINT FK_BCDB23BE700047D2 FOREIGN KEY (ticket_id) REFERENCES tickets (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE ticket_community_topics_links ADD CONSTRAINT FK_BCDB23BED249A887 FOREIGN KEY (topic_id) REFERENCES community_topics (id) ON DELETE CASCADE');
    }

    public function run()
    {
    }
}
