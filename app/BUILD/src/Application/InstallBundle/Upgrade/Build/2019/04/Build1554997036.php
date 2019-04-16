<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1554997036 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE custom_def_download (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, app_id INT DEFAULT NULL, sys_name varchar(100) DEFAULT NULL, js_class VARCHAR(255) NOT NULL, has_form_template TINYINT(1) NOT NULL, has_display_template TINYINT(1) NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, handler_class VARCHAR(255) DEFAULT NULL, options LONGBLOB NOT NULL COMMENT \'(DC2Type:array)\', is_user_enabled TINYINT(1) NOT NULL, is_enabled TINYINT(1) NOT NULL, display_order INT NOT NULL, default_value VARCHAR(500) DEFAULT NULL, is_agent_field TINYINT(1) NOT NULL, INDEX IDX_66AF1BF0727ACA70 (parent_id), INDEX IDX_66AF1BF07987212D (app_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE custom_data_download (id INT AUTO_INCREMENT NOT NULL, download_id INT NOT NULL, field_id INT NOT NULL, root_field_id INT NOT NULL, value BIGINT NOT NULL, input LONGTEXT NOT NULL, INDEX IDX_38DA0557C667AEAB (download_id), INDEX IDX_38DA0557443707B0 (field_id), INDEX IDX_38DA05573F6A6D56 (root_field_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE custom_def_download ADD CONSTRAINT FK_66AF1BF0727ACA70 FOREIGN KEY (parent_id) REFERENCES custom_def_download (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE custom_def_download ADD CONSTRAINT FK_66AF1BF07987212D FOREIGN KEY (app_id) REFERENCES app_instances (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE custom_data_download ADD CONSTRAINT FK_38DA0557C667AEAB FOREIGN KEY (download_id) REFERENCES downloads (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE custom_data_download ADD CONSTRAINT FK_38DA0557443707B0 FOREIGN KEY (field_id) REFERENCES custom_def_download (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE custom_data_download ADD CONSTRAINT FK_38DA05573F6A6D56 FOREIGN KEY (root_field_id) REFERENCES custom_def_download (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX UNIQ_CUSTOM_DATA_DOWNLOAD_SYS_NAME ON custom_def_download (sys_name)');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
