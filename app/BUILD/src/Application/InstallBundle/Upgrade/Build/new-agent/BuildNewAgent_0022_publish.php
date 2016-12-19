<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0022_publish extends AbstractBuild
{
    public function run()
    {
        $this->out('Modify article_pending_create table');
        $this->execMutateSql('ALTER TABLE article_pending_create ADD assigned_person_id INT DEFAULT NULL');
        $this->execMutateSql('ALTER TABLE article_pending_create ADD INDEX IDX_27A971C358DA0EE5 (assigned_person_id), ADD CONSTRAINT FK_27A971C358DA0EE5 FOREIGN KEY (assigned_person_id) REFERENCES people (id) ON DELETE CASCADE');

        $this->out('Modify downloads table');
        $this->execMutateSql('ALTER TABLE downloads ADD date_last_comment DATETIME DEFAULT NULL, CHANGE date_updated date_updated DATETIME DEFAULT NULL');
        $this->execMutateSql('ALTER TABLE downloads ADD INDEX date_updated_idx (date_updated), ADD INDEX date_last_comment_idx (date_last_comment)');

        $this->out('Modify articles table');
        $this->execMutateSql('ALTER TABLE articles CHANGE date_updated date_updated DATETIME DEFAULT NULL');

        $this->out('Modify news table');
        $this->execMutateSql('ALTER TABLE news ADD date_updated DATETIME DEFAULT NULL, ADD date_last_comment DATETIME DEFAULT NULL');
        $this->execMutateSql('ALTER TABLE news ADD INDEX date_updated_idx (date_updated), ADD INDEX date_last_comment_idx (date_last_comment)');

        $this->out('Modify feedback table');
        $this->execMutateSql('ALTER TABLE feedback ADD is_reviewed TINYINT(1) NOT NULL, ADD date_updated DATETIME DEFAULT NULL, ADD date_last_comment DATETIME DEFAULT NULL, DROP validating');
        $this->execMutateSql("UPDATE feedback SET is_reviewed = 1 WHERE hidden_status != 'validating'");
        $this->execMutateSql('ALTER TABLE feedback ADD INDEX date_updated_idx (date_updated), ADD INDEX date_last_comment_idx (date_last_comment)');

        $this->out('Modify knowledge base subscription table');
        $this->execMutateSql('ALTER TABLE kb_subscriptions ADD root_category TINYINT(1) DEFAULT NULL');
        $this->execMutateSql('ALTER TABLE kb_subscriptions ADD INDEX root_category_idx (root_category)');

        $this->out('Modify news subscription table');
        $this->execMutateSql('ALTER TABLE news_subscriptions ADD root_category TINYINT(1) DEFAULT NULL');
        $this->execMutateSql('ALTER TABLE news_subscriptions ADD INDEX root_category_idx (root_category)');

        foreach ([
            'article_comments',
            'download_comments',
            'feedback_comments',
            'news_comments',
        ] as $t) {
            $this->out("Modify $t table");
            $this->execMutateSql("UPDATE $t SET status = 'deleted' WHERE validating = 1");
            $this->execMutateSql("ALTER TABLE $t DROP validating");
        }
    }
}

//[[build:1460678409]]
