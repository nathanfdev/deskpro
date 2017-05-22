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

class Build1493905797 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', "CREATE TABLE app2_app_asset (id INT AUTO_INCREMENT NOT NULL, app_id INT NOT NULL, path VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, INDEX IDX_54BB06CF7987212D (app_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->execDbQuery('default', "CREATE TABLE app2_app_instance (id INT AUTO_INCREMENT NOT NULL, app_id INT NOT NULL, name VARCHAR(255) NOT NULL, scope VARCHAR(100) NOT NULL, settings LONGTEXT DEFAULT NULL, secret_key LONGTEXT DEFAULT NULL, createdAt DATETIME DEFAULT NULL, INDEX IDX_B1B171047987212D (app_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->execDbQuery('default', "CREATE TABLE app2_app_asset_blob (id INT AUTO_INCREMENT NOT NULL, app_id INT NOT NULL, blob_id INT DEFAULT NULL, path VARCHAR(255) NOT NULL, blob_authcode VARCHAR(255) NOT NULL, INDEX IDX_B4A87CA07987212D (app_id), UNIQUE INDEX UNIQ_B4A87CA0ED3E8EA5 (blob_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->execDbQuery('default', "CREATE TABLE app2_app_state (id INT AUTO_INCREMENT NOT NULL, app_instance_id INT NOT NULL, owner_id INT DEFAULT NULL, scope VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, value LONGTEXT NOT NULL, ownerId INT DEFAULT NULL, targetId INT DEFAULT NULL, createdAt DATETIME DEFAULT NULL, INDEX IDX_F5878E6863B454A1 (app_instance_id), INDEX IDX_F5878E687E3C61F9 (owner_id), UNIQUE INDEX state_unique (app_instance_id, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->execDbQuery('default', "CREATE TABLE app2_app (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, manifest LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->execDbQuery('default', "ALTER TABLE app2_app_asset ADD CONSTRAINT FK_54BB06CF7987212D FOREIGN KEY (app_id) REFERENCES app2_app (id)");
        $this->execDbQuery('default', "ALTER TABLE app2_app_instance ADD CONSTRAINT FK_B1B171047987212D FOREIGN KEY (app_id) REFERENCES app2_app (id)");
        $this->execDbQuery('default', "ALTER TABLE app2_app_asset_blob ADD CONSTRAINT FK_B4A87CA07987212D FOREIGN KEY (app_id) REFERENCES app2_app (id)");
        $this->execDbQuery('default', "ALTER TABLE app2_app_asset_blob ADD CONSTRAINT FK_B4A87CA0ED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id)");
        $this->execDbQuery('default', "ALTER TABLE app2_app_state ADD CONSTRAINT FK_F5878E6863B454A1 FOREIGN KEY (app_instance_id) REFERENCES app2_app_instance (id)");
        $this->execDbQuery('default', "ALTER TABLE app2_app_state ADD CONSTRAINT FK_F5878E687E3C61F9 FOREIGN KEY (owner_id) REFERENCES people (id)");
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
