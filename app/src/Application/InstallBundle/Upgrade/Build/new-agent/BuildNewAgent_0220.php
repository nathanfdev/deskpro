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

/**
 * DeskPRO.
 */
namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0220 extends AbstractBuild
{
    public function run()
    {
        $this->execMutateSql("CREATE TABLE brand_assets (id INT AUTO_INCREMENT NOT NULL, brand_id INT DEFAULT NULL, blob_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, tags LONGTEXT NOT NULL COMMENT '(DC2Type:simple_array)', date_created DATETIME NOT NULL, date_updated DATETIME NOT NULL, INDEX IDX_FA59018044F5D008 (brand_id), UNIQUE INDEX UNIQ_FA590180ED3E8EA5 (blob_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->execMutateSql('ALTER TABLE brand_assets ADD CONSTRAINT FK_FA59018044F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE CASCADE');
        $this->execMutateSql('ALTER TABLE brand_assets ADD CONSTRAINT FK_FA590180ED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE CASCADE');
    }
}

//[[build:1456790422]]

