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

namespace Application\InstallBundle\Upgrade\Build;

class Build1466087195 extends AbstractBuild
{
    public function run()
    {
        $this->out('Url in brands');
        $this->execDbQuery('default', 'ALTER TABLE brands ADD url VARCHAR(255)');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX UNIQ_7EA24434F47645AE ON brands (url)');
        $this->execDbQuery('default', 'ALTER TABLE brands ADD logo_blob_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE brands ADD CONSTRAINT FK_7EA24434D91464D5 FOREIGN KEY (logo_blob_id) REFERENCES blobs (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX UNIQ_7EA24434D91464D5 ON brands (logo_blob_id)');

        $this->out('Content categories brand');
        $this->execDbQuery('default', 'ALTER TABLE article_categories ADD brand_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE article_categories ADD CONSTRAINT FK_62A97E944F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_62A97E944F5D008 ON article_categories (brand_id)');
        $this->execDbQuery('default', 'ALTER TABLE download_categories ADD brand_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE download_categories ADD CONSTRAINT FK_3317F1544F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_3317F1544F5D008 ON download_categories (brand_id)');
        $this->execDbQuery('default', 'ALTER TABLE news_categories ADD brand_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE news_categories ADD CONSTRAINT FK_D68C911144F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_D68C911144F5D008 ON news_categories (brand_id)');

        $this->out('Departments brand');
        $this->execDbQuery('default', 'CREATE TABLE department_to_brand (department_id INT NOT NULL, brand_id INT NOT NULL, INDEX IDX_2ED0D242AE80F5DF (department_id), INDEX IDX_2ED0D24244F5D008 (brand_id), PRIMARY KEY(department_id, brand_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('default', 'ALTER TABLE department_to_brand ADD CONSTRAINT FK_2ED0D242AE80F5DF FOREIGN KEY (department_id) REFERENCES departments (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE department_to_brand ADD CONSTRAINT FK_2ED0D24244F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE CASCADE');

        $this->out('Glossary words brand');
        $this->execDbQuery('default', 'ALTER TABLE glossary_words ADD brand_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE glossary_words ADD CONSTRAINT FK_1A8003DA44F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_1A8003DA44F5D008 ON glossary_words (brand_id)');
        $this->execDbQuery('default', 'DROP INDEX UNIQ_1A8003DAC3F17511 ON glossary_words;');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX glossary_words_word_brand_id_uindex ON glossary_words (word, brand_id);');

        $this->out('Add tickets link to brand');
        $instructions   = [];
        $instructions[] = 'ADD brand_id INT DEFAULT NULL';
        $instructions[] = 'ADD CONSTRAINT FK_54469DF444F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL';
        $instructions[] = 'CREATE INDEX IDX_54469DF444F5D008 ON tickets (brand_id)';

        $this->execSlowAlterTable('tickets', implode(', ', $instructions));

        $instructions = ['ADD brand_id INT DEFAULT NULL'];
        $this->execSlowAlterTable('tickets_search_active', implode(', ', $instructions));
    }
}
