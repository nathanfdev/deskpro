<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

class Build1448479966 extends AbstractBuild
{
    public function run()
    {
        $this->out('Changes to Themes and Brands');
        $this->execMutateSql("CREATE TABLE theme_sets (id INT AUTO_INCREMENT NOT NULL, brand_id INT DEFAULT NULL, theme_id VARCHAR(255) NOT NULL, options LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', INDEX IDX_DE4AB1EC44F5D008 (brand_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->execMutateSql("CREATE TABLE theme_set_assets (id INT AUTO_INCREMENT NOT NULL, theme_set_id INT DEFAULT NULL, blob_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, tags LONGTEXT NOT NULL COMMENT '(DC2Type:simple_array)', date_created DATETIME NOT NULL, date_updated DATETIME NOT NULL, INDEX IDX_4E537458C0C33964 (theme_set_id), UNIQUE INDEX UNIQ_4E537458ED3E8EA5 (blob_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->execMutateSql('ALTER TABLE theme_sets ADD CONSTRAINT FK_DE4AB1EC44F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE CASCADE');
        $this->execMutateSql('ALTER TABLE theme_set_assets ADD CONSTRAINT FK_4E537458C0C33964 FOREIGN KEY (theme_set_id) REFERENCES theme_sets (id) ON DELETE CASCADE');
        $this->execMutateSql('ALTER TABLE theme_set_assets ADD CONSTRAINT FK_4E537458ED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE CASCADE');
        $this->execMutateSql('ALTER TABLE brands ADD theme_set_id INT DEFAULT NULL, ADD edit_theme_set_id INT DEFAULT NULL, DROP theme_id');
        $this->execMutateSql('ALTER TABLE brands ADD CONSTRAINT FK_7EA24434C0C33964 FOREIGN KEY (theme_set_id) REFERENCES theme_sets (id)');
        $this->execMutateSql('ALTER TABLE brands ADD CONSTRAINT FK_7EA24434F1B7F8A2 FOREIGN KEY (edit_theme_set_id) REFERENCES theme_sets (id)');
        $this->execMutateSql('CREATE UNIQUE INDEX UNIQ_7EA24434C0C33964 ON brands (theme_set_id)');
        $this->execMutateSql('CREATE UNIQUE INDEX UNIQ_7EA24434F1B7F8A2 ON brands (edit_theme_set_id)');
        $this->execMutateSql('ALTER TABLE templates DROP FOREIGN KEY FK_6F287D8E44F5D008');
        $this->execMutateSql('DROP INDEX IDX_6F287D8E44F5D008 ON templates');
        $this->execMutateSql('ALTER TABLE templates DROP theme_id, CHANGE brand_id theme_set_id INT DEFAULT NULL');
        $this->execMutateSql('ALTER TABLE templates ADD CONSTRAINT FK_6F287D8EC0C33964 FOREIGN KEY (theme_set_id) REFERENCES theme_sets (id)');
        $this->execMutateSql('CREATE UNIQUE INDEX UNIQ_6F287D8EC0C33964 ON templates (theme_set_id)');
    }
}
