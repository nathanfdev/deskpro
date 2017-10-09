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

class Build1507539182 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE snippet_changelog (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, snippet_id INT DEFAULT NULL, language_id INT DEFAULT NULL, date_created DATETIME NOT NULL, content LONGTEXT NOT NULL, type VARCHAR(255) DEFAULT NULL, INDEX IDX_1B8B5728217BBB47 (person_id), INDEX IDX_1B8B57286E34B975 (snippet_id), INDEX IDX_1B8B572882F1BAF4 (language_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE snippet_changelog_blob (snippet_changelog_id INT NOT NULL, blob_id INT NOT NULL, INDEX IDX_E67B9F2DF8F4348 (snippet_changelog_id), INDEX IDX_E67B9F2ED3E8EA5 (blob_id), PRIMARY KEY(snippet_changelog_id, blob_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE snippet_changelog ADD CONSTRAINT FK_1B8B5728217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE snippet_changelog ADD CONSTRAINT FK_1B8B57286E34B975 FOREIGN KEY (snippet_id) REFERENCES snippets (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE snippet_changelog ADD CONSTRAINT FK_1B8B572882F1BAF4 FOREIGN KEY (language_id) REFERENCES languages (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE snippet_changelog_blob ADD CONSTRAINT FK_E67B9F2DF8F4348 FOREIGN KEY (snippet_changelog_id) REFERENCES snippet_changelog (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE snippet_changelog_blob ADD CONSTRAINT FK_E67B9F2ED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE snippets ADD date_created DATETIME NOT NULL');
    }

    public function run()
    {
    }
}
