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

// NOTE: I used the OnlineBuildInterface interface because
//       it looks like your schema changes ARE backwards compatible with the previous version.
//       You should double-check this yourself though. If there are breaking changes, use BlockingBuildInterface instead.

// NOTE: I have added the SkipPostBuildInterface interface because
//       it looks like you do not have any changes that require PostBuild to run.
//       You should double-check this yourself though. Remove the SkipPostBuildInterface interface if necessary.

// Please remove these NOTE comments after you have checked the code.

class Build1504707791 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE snippet_use_log (id INT AUTO_INCREMENT NOT NULL, ticket_message_id INT DEFAULT NULL, chat_message_id INT DEFAULT NULL, snippet_id INT DEFAULT NULL, language_id INT DEFAULT NULL, person_id INT DEFAULT NULL, rating LONGTEXT NOT NULL, type VARCHAR(255) DEFAULT NULL, INDEX IDX_43190050C5E9817D (ticket_message_id), INDEX IDX_43190050948B568F (chat_message_id), INDEX IDX_431900506E34B975 (snippet_id), INDEX IDX_4319005082F1BAF4 (language_id), INDEX IDX_43190050217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE snippet_use_log ADD CONSTRAINT FK_43190050C5E9817D FOREIGN KEY (ticket_message_id) REFERENCES tickets_messages (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE snippet_use_log ADD CONSTRAINT FK_43190050948B568F FOREIGN KEY (chat_message_id) REFERENCES chat_messages (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE snippet_use_log ADD CONSTRAINT FK_431900506E34B975 FOREIGN KEY (snippet_id) REFERENCES snippets (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE snippet_use_log ADD CONSTRAINT FK_4319005082F1BAF4 FOREIGN KEY (language_id) REFERENCES languages (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE snippet_use_log ADD CONSTRAINT FK_43190050217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
