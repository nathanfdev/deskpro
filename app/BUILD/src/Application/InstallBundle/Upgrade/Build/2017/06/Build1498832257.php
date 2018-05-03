<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1498832257 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE snippets (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, shortcut_code VARCHAR(255) NOT NULL, title LONGTEXT NOT NULL, types LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', is_draft TINYINT(1) NOT NULL, ownership_global TINYINT(1) NOT NULL, visible_global TINYINT(1) NOT NULL, INDEX IDX_ED21F5DC217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE snippet_ownership_teams (snippet_id INT NOT NULL, agent_team_id INT NOT NULL, INDEX IDX_B8C714476E34B975 (snippet_id), INDEX IDX_B8C71447FB3FBA04 (agent_team_id), PRIMARY KEY(snippet_id, agent_team_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE snippet_visible_departments (snippet_id INT NOT NULL, department_id INT NOT NULL, INDEX IDX_341E506F6E34B975 (snippet_id), INDEX IDX_341E506FAE80F5DF (department_id), PRIMARY KEY(snippet_id, department_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE snippet_labels (snippet_id INT NOT NULL, label VARCHAR(255) NOT NULL, INDEX IDX_E150D9286E34B975 (snippet_id), INDEX label (label), PRIMARY KEY(snippet_id, label)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE snippet_translations (id INT AUTO_INCREMENT NOT NULL, snippet_id INT DEFAULT NULL, language_id INT DEFAULT NULL, content LONGTEXT NOT NULL, title LONGTEXT DEFAULT NULL, INDEX IDX_25B795986E34B975 (snippet_id), INDEX IDX_25B7959882F1BAF4 (language_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE snippet_translation_blob (snippet_translation_id INT NOT NULL, blob_id INT NOT NULL, INDEX IDX_D7E238A1B7939ABA (snippet_translation_id), INDEX IDX_D7E238A1ED3E8EA5 (blob_id), PRIMARY KEY(snippet_translation_id, blob_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE snippets ADD CONSTRAINT FK_ED21F5DC217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE snippet_ownership_teams ADD CONSTRAINT FK_B8C714476E34B975 FOREIGN KEY (snippet_id) REFERENCES snippets (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE snippet_ownership_teams ADD CONSTRAINT FK_B8C71447FB3FBA04 FOREIGN KEY (agent_team_id) REFERENCES agent_teams (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE snippet_visible_departments ADD CONSTRAINT FK_341E506F6E34B975 FOREIGN KEY (snippet_id) REFERENCES snippets (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE snippet_visible_departments ADD CONSTRAINT FK_341E506FAE80F5DF FOREIGN KEY (department_id) REFERENCES departments (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE snippet_labels ADD CONSTRAINT FK_E150D9286E34B975 FOREIGN KEY (snippet_id) REFERENCES snippets (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE snippet_translations ADD CONSTRAINT FK_25B795986E34B975 FOREIGN KEY (snippet_id) REFERENCES snippets (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE snippet_translations ADD CONSTRAINT FK_25B7959882F1BAF4 FOREIGN KEY (language_id) REFERENCES languages (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE snippet_translation_blob ADD CONSTRAINT FK_D7E238A1B7939ABA FOREIGN KEY (snippet_translation_id) REFERENCES snippet_translations (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE snippet_translation_blob ADD CONSTRAINT FK_D7E238A1ED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
