<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace Application\InstallBundle\Upgrade\Build;

class Build1487587808 extends AbstractBuild
{
    public function run()
    {
        $this->out('My Upgrade Class');
        $this->execDbQuery('default', 'CREATE TABLE manuals (id INT AUTO_INCREMENT NOT NULL, brand_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, display_order INT NOT NULL, UNIQUE INDEX UNIQ_8717121C989D9B62 (slug), INDEX IDX_8717121C44F5D008 (brand_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('default', 'CREATE TABLE manual_topics (id INT AUTO_INCREMENT NOT NULL, manual_id INT DEFAULT NULL, person_id INT DEFAULT NULL, language_id INT DEFAULT NULL, slug VARCHAR(100) NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, content_input LONGTEXT NOT NULL, content_input_type VARCHAR(100) DEFAULT NULL, view_count INT NOT NULL, total_rating INT NOT NULL, num_comments INT NOT NULL, num_ratings INT NOT NULL, status VARCHAR(15) NOT NULL, hidden_status VARCHAR(15) DEFAULT NULL, display_order INT NOT NULL, date_created DATETIME NOT NULL, date_updated DATETIME DEFAULT NULL, date_published DATETIME DEFAULT NULL, date_last_comment DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_DF8545B8989D9B62 (slug), INDEX IDX_DF8545B89BA073D6 (manual_id), INDEX IDX_DF8545B8217BBB47 (person_id), INDEX IDX_DF8545B882F1BAF4 (language_id), INDEX date_published_idx (date_published), INDEX date_updated_idx (date_updated), INDEX date_last_comment_idx (date_last_comment), INDEX status_idx (status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('default', 'CREATE TABLE manual_topic_comments (id INT AUTO_INCREMENT NOT NULL, manual_topic_id INT DEFAULT NULL, person_id INT DEFAULT NULL, ip_address VARCHAR(30) NOT NULL, visitor_id VARCHAR(120) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, content LONGTEXT NOT NULL, status VARCHAR(30) NOT NULL, is_reviewed TINYINT(1) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_DAD916E9BDD0A6A8 (manual_topic_id), INDEX IDX_DAD916E9217BBB47 (person_id), INDEX status_idx (status, is_reviewed), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('default', 'CREATE TABLE manual_topic_revisions (id INT AUTO_INCREMENT NOT NULL, manual_topic_id INT DEFAULT NULL, person_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, status VARCHAR(30) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_8B59F60FBDD0A6A8 (manual_topic_id), INDEX IDX_8B59F60F217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('default', 'CREATE TABLE manual_topic_slug_history (id INT AUTO_INCREMENT NOT NULL, manual_topic_id INT NOT NULL, date_created DATETIME NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_CB5A897F989D9B62 (slug), INDEX IDX_CB5A897FBDD0A6A8 (manual_topic_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('default', 'ALTER TABLE manuals ADD CONSTRAINT FK_8717121C44F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE manual_topics ADD CONSTRAINT FK_DF8545B89BA073D6 FOREIGN KEY (manual_id) REFERENCES manuals (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE manual_topics ADD CONSTRAINT FK_DF8545B8217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE manual_topics ADD CONSTRAINT FK_DF8545B882F1BAF4 FOREIGN KEY (language_id) REFERENCES languages (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE manual_topic_comments ADD CONSTRAINT FK_DAD916E9BDD0A6A8 FOREIGN KEY (manual_topic_id) REFERENCES manual_topics (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE manual_topic_comments ADD CONSTRAINT FK_DAD916E9217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE manual_topic_revisions ADD CONSTRAINT FK_8B59F60FBDD0A6A8 FOREIGN KEY (manual_topic_id) REFERENCES manual_topics (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE manual_topic_revisions ADD CONSTRAINT FK_8B59F60F217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE manual_topic_slug_history ADD CONSTRAINT FK_CB5A897FBDD0A6A8 FOREIGN KEY (manual_topic_id) REFERENCES manual_topics (id) ON DELETE CASCADE');
    }
}
