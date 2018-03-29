<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1488982984 extends AbstractBuild
{
    public function run()
    {
        $this->out('Add guide tables');
        $this->execDbQuery('default', 'CREATE TABLE guides (id INT AUTO_INCREMENT NOT NULL, brand_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, display_order INT NOT NULL, UNIQUE INDEX UNIQ_4D7795EF989D9B62 (slug), INDEX IDX_4D7795EF44F5D008 (brand_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('default', 'CREATE TABLE guide2usergroup (guide_id INT NOT NULL, usergroup_id INT NOT NULL, INDEX IDX_23FA1731D7ED1D4B (guide_id), INDEX IDX_23FA1731D2112630 (usergroup_id), PRIMARY KEY(guide_id, usergroup_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('default', 'CREATE TABLE topics (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, guide_id INT DEFAULT NULL, person_id INT DEFAULT NULL, language_id INT DEFAULT NULL, slug VARCHAR(100) NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, content_input LONGTEXT NOT NULL, content_input_type VARCHAR(100) DEFAULT NULL, view_count INT NOT NULL, total_rating INT NOT NULL, num_comments INT NOT NULL, num_ratings INT NOT NULL, status VARCHAR(15) NOT NULL, hidden_status VARCHAR(15) DEFAULT NULL, no_content TINYINT(1) NOT NULL, display_order INT NOT NULL, date_created DATETIME NOT NULL, date_updated DATETIME DEFAULT NULL, date_published DATETIME DEFAULT NULL, date_last_comment DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_91F64639989D9B62 (slug), INDEX IDX_91F64639727ACA70 (parent_id), INDEX IDX_91F64639D7ED1D4B (guide_id), INDEX IDX_91F64639217BBB47 (person_id), INDEX IDX_91F6463982F1BAF4 (language_id), INDEX date_published_idx (date_published), INDEX date_updated_idx (date_updated), INDEX date_last_comment_idx (date_last_comment), INDEX status_idx (status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('default', 'CREATE TABLE topic_comments (id INT AUTO_INCREMENT NOT NULL, topic_id INT DEFAULT NULL, person_id INT DEFAULT NULL, ip_address VARCHAR(30) NOT NULL, visitor_id VARCHAR(120) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, content LONGTEXT NOT NULL, status VARCHAR(30) NOT NULL, is_reviewed TINYINT(1) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_A9AF1B2C1F55203D (topic_id), INDEX IDX_A9AF1B2C217BBB47 (person_id), INDEX status_idx (status, is_reviewed), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('default', 'CREATE TABLE topic_revisions (id INT AUTO_INCREMENT NOT NULL, topic_id INT DEFAULT NULL, person_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, status VARCHAR(30) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_6024B63D1F55203D (topic_id), INDEX IDX_6024B63D217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('default', 'CREATE TABLE topic_slug_history (id INT AUTO_INCREMENT NOT NULL, topic_id INT NOT NULL, date_created DATETIME NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_3278FAEE989D9B62 (slug), INDEX IDX_3278FAEE1F55203D (topic_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('default', 'ALTER TABLE guides ADD CONSTRAINT FK_4D7795EF44F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE guide2usergroup ADD CONSTRAINT FK_23FA1731D7ED1D4B FOREIGN KEY (guide_id) REFERENCES guides (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE guide2usergroup ADD CONSTRAINT FK_23FA1731D2112630 FOREIGN KEY (usergroup_id) REFERENCES usergroups (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE topics ADD CONSTRAINT FK_91F64639727ACA70 FOREIGN KEY (parent_id) REFERENCES topics (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE topics ADD CONSTRAINT FK_91F64639D7ED1D4B FOREIGN KEY (guide_id) REFERENCES guides (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE topics ADD CONSTRAINT FK_91F64639217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE topics ADD CONSTRAINT FK_91F6463982F1BAF4 FOREIGN KEY (language_id) REFERENCES languages (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE topic_comments ADD CONSTRAINT FK_A9AF1B2C1F55203D FOREIGN KEY (topic_id) REFERENCES topics (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE topic_comments ADD CONSTRAINT FK_A9AF1B2C217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE topic_revisions ADD CONSTRAINT FK_6024B63D1F55203D FOREIGN KEY (topic_id) REFERENCES topics (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE topic_revisions ADD CONSTRAINT FK_6024B63D217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE topic_slug_history ADD CONSTRAINT FK_3278FAEE1F55203D FOREIGN KEY (topic_id) REFERENCES topics (id) ON DELETE CASCADE');
    }
}
