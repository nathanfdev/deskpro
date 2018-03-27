<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1495797154 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE app2_app (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, `manifest` LONGTEXT NOT NULL, UNIQUE INDEX name_unique (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE app2_app_asset_blob (id INT AUTO_INCREMENT NOT NULL, app_id INT NOT NULL, blob_id INT DEFAULT NULL, path VARCHAR(255) NOT NULL, blob_authcode VARCHAR(255) NOT NULL, INDEX IDX_B4A87CA07987212D (app_id), UNIQUE INDEX UNIQ_B4A87CA0ED3E8EA5 (blob_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', "CREATE TABLE app2_app_instance (id INT AUTO_INCREMENT NOT NULL, app_id INT NOT NULL, name VARCHAR(255) NOT NULL, scope VARCHAR(100) NOT NULL, settings LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', secret_key LONGTEXT DEFAULT NULL, createdAt DATETIME DEFAULT NULL, INDEX IDX_B1B171047987212D (app_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->execDbQuery('default', 'CREATE TABLE app2_app_state (id INT AUTO_INCREMENT NOT NULL, app_instance_id INT NOT NULL, owner_id INT DEFAULT NULL, scope VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, value LONGTEXT NOT NULL, ownerId INT DEFAULT NULL, targetId INT DEFAULT NULL, createdAt DATETIME DEFAULT NULL, INDEX IDX_F5878E6863B454A1 (app_instance_id), INDEX IDX_F5878E687E3C61F9 (owner_id), UNIQUE INDEX state_unique (app_instance_id, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE app2_app_asset_blob ADD CONSTRAINT FK_B4A87CA07987212D FOREIGN KEY (app_id) REFERENCES app2_app (id)');
        $this->execDbQuery('default', 'ALTER TABLE app2_app_asset_blob ADD CONSTRAINT FK_B4A87CA0ED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id)');
        $this->execDbQuery('default', 'ALTER TABLE app2_app_instance ADD CONSTRAINT FK_B1B171047987212D FOREIGN KEY (app_id) REFERENCES app2_app (id)');
        $this->execDbQuery('default', 'ALTER TABLE app2_app_state ADD CONSTRAINT FK_F5878E6863B454A1 FOREIGN KEY (app_instance_id) REFERENCES app2_app_instance (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE app2_app_state ADD CONSTRAINT FK_F5878E687E3C61F9 FOREIGN KEY (owner_id) REFERENCES people (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
