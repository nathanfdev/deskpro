<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1574273658 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
        if (!$this->getSchemaHelper()->getSchemaManager()->tablesExist(['content_templates', 'content_template_attachments'])) {
            $this->execDbQueryQuiet('default', "CREATE TABLE content_templates (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, type VARCHAR(20) NOT NULL, title VARCHAR(255) NOT NULL, date_created DATETIME NOT NULL, date_updated DATETIME NOT NULL, template LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', INDEX IDX_5FCF358D217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
            $this->execDbQueryQuiet('default', 'CREATE TABLE content_template_attachments (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, content_template_id INT DEFAULT NULL, blob_id INT DEFAULT NULL, date_created DATETIME NOT NULL, date_updated DATETIME NOT NULL, INDEX IDX_832870B8217BBB47 (person_id), INDEX IDX_832870B856F0A53E (content_template_id), INDEX IDX_832870B8ED3E8EA5 (blob_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
            $this->execDbQueryQuiet('default', 'ALTER TABLE content_templates ADD CONSTRAINT FK_5FCF358D217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
            $this->execDbQueryQuiet('default', 'ALTER TABLE content_template_attachments ADD CONSTRAINT FK_832870B8217BBB47 FOREIGN KEY (person_id) REFERENCES people (id)');
            $this->execDbQueryQuiet('default', 'ALTER TABLE content_template_attachments ADD CONSTRAINT FK_832870B856F0A53E FOREIGN KEY (content_template_id) REFERENCES content_templates (id) ON DELETE CASCADE');
            $this->execDbQueryQuiet('default', 'ALTER TABLE content_template_attachments ADD CONSTRAINT FK_832870B8ED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE CASCADE');
        }
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
