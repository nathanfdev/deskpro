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

// NOTE: I used the BlockingBuildInterface interface because
//       it looks like your schema changes are NOT backwards compatible with the previous version.
//       You should double-check this yourself though. If they are backwards compatible, use OnlineBuildInterface instead.

// NOTE: I have added the SkipPostBuildInterface interface because
//       it looks like you do not have any changes that require PostBuild to run.
//       You should double-check this yourself though. Remove the SkipPostBuildInterface interface if necessary.

// Please remove these NOTE comments after you have checked the code.

class Build1498552094 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE snippets (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, shortcut_code VARCHAR(255) NOT NULL, title LONGTEXT NOT NULL, types LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', is_draft TINYINT(1) NOT NULL, ownership_global TINYINT(1) NOT NULL, visible_global TINYINT(1) NOT NULL, INDEX IDX_ED21F5DC217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE snippet_ownership_teams (snippet_id INT NOT NULL, agent_team_id INT NOT NULL, INDEX IDX_B8C714476E34B975 (snippet_id), INDEX IDX_B8C71447FB3FBA04 (agent_team_id), PRIMARY KEY(snippet_id, agent_team_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE snippet_visible_departments (snippet_id INT NOT NULL, department_id INT NOT NULL, INDEX IDX_341E506F6E34B975 (snippet_id), INDEX IDX_341E506FAE80F5DF (department_id), PRIMARY KEY(snippet_id, department_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE snippet_labels (snippet_id INT NOT NULL, label VARCHAR(255) NOT NULL, INDEX IDX_E150D9286E34B975 (snippet_id), INDEX label (label), PRIMARY KEY(snippet_id, label)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE snippet_translations (id INT AUTO_INCREMENT NOT NULL, snippet_id INT DEFAULT NULL, language_id INT DEFAULT NULL, content LONGTEXT NOT NULL, title LONGTEXT NOT NULL, INDEX IDX_25B795986E34B975 (snippet_id), INDEX IDX_25B7959882F1BAF4 (language_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
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
