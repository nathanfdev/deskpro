<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1469786838 extends AbstractBuild
{
    public function run()
    {
        $this->out('Url in brands');
        $this->execDbQuery('default', 'ALTER TABLE brands ADD url VARCHAR(255)');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX UNIQ_7EA24434F47645AE ON brands (url)');
        $this->execDbQuery('default', 'ALTER TABLE brands ADD logo_blob_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE brands ADD CONSTRAINT FK_7EA24434D91464D5 FOREIGN KEY (logo_blob_id) REFERENCES blobs (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX UNIQ_7EA24434D91464D5 ON brands (logo_blob_id)');

        $brands = $this->getDbConnection('default')->fetchAll('SELECT * FROM `brands`');
        $brand  = current($brands);

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

        $this->out('Bind categories to brand to keep portal working');

        $this->execDbQuery('default', sprintf('UPDATE `article_categories` SET `brand_id` = %d', $brand['id']));
        $this->execDbQuery('default', sprintf('UPDATE `download_categories` SET `brand_id` = %d', $brand['id']));
        $this->execDbQuery('default', sprintf('UPDATE `news_categories` SET `brand_id` = %d', $brand['id']));

        $this->out('Departments brand');
        $this->execDbQuery('default', 'CREATE TABLE department_to_brand (department_id INT NOT NULL, brand_id INT NOT NULL, INDEX IDX_2ED0D242AE80F5DF (department_id), INDEX IDX_2ED0D24244F5D008 (brand_id), PRIMARY KEY(department_id, brand_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('default', 'ALTER TABLE department_to_brand ADD CONSTRAINT FK_2ED0D242AE80F5DF FOREIGN KEY (department_id) REFERENCES departments (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE department_to_brand ADD CONSTRAINT FK_2ED0D24244F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE CASCADE');

        $this->out('Bind departments to existing brand');

        $depIds    = $this->getDbConnection('default')->fetchAllCol('SELECT id FROM departments');
        $statement = $this->getDbConnection('default')->prepare('
            INSERT INTO `department_to_brand` SET
            `department_id` = :department, `brand_id` = :brand
        ');
        foreach ($depIds as $depId) {
            $statement->execute([
                'department' => $depId,
                'brand'      => $brand['id'],
            ]);
        }

        $this->out('Glossary words brand');
        $this->execDbQuery('default', 'ALTER TABLE glossary_words ADD brand_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE glossary_words ADD CONSTRAINT FK_1A8003DA44F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_1A8003DA44F5D008 ON glossary_words (brand_id)');
        $this->execDbQuery('default', 'DROP INDEX UNIQ_1A8003DAC3F17511 ON glossary_words;');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX glossary_words_word_brand_id_uindex ON glossary_words (word, brand_id);');

        $this->execDbQuery('default', sprintf('UPDATE `glossary_words` SET `brand_id` = %d', $brand['id']));
    }
}
