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

class Build1444325594 extends AbstractBuild
{
    public function run()
    {
        $this->out('Add ticket_object_use_logs');
        $tables = $this->getSchemaHelper()->getSchemaManager()->listTableNames();
        if (!in_array('ticket_object_use_logs', $tables)) {
            $this->execMutateSql('CREATE TABLE ticket_object_use_logs (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, ticket_id INT DEFAULT NULL, snippet_id INT DEFAULT NULL, macro_id INT DEFAULT NULL, object_type VARCHAR(100) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_BBE1D3E1217BBB47 (person_id), INDEX IDX_BBE1D3E1700047D2 (ticket_id), INDEX IDX_BBE1D3E16E34B975 (snippet_id), INDEX IDX_BBE1D3E1F43A187E (macro_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
            $this->execMutateSql('ALTER TABLE ticket_object_use_logs ADD CONSTRAINT FK_BBE1D3E1217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
            $this->execMutateSql('ALTER TABLE ticket_object_use_logs ADD CONSTRAINT FK_BBE1D3E1700047D2 FOREIGN KEY (ticket_id) REFERENCES tickets (id) ON DELETE SET NULL');
            $this->execMutateSql('ALTER TABLE ticket_object_use_logs ADD CONSTRAINT FK_BBE1D3E16E34B975 FOREIGN KEY (snippet_id) REFERENCES text_snippets (id) ON DELETE SET NULL');
            $this->execMutateSql('ALTER TABLE ticket_object_use_logs ADD CONSTRAINT FK_BBE1D3E1F43A187E FOREIGN KEY (macro_id) REFERENCES ticket_macros (id) ON DELETE SET NULL');
        }

        $this->out('Remove old unused text_snippet_logs');
        $this->execMutateSql('DROP TABLE IF EXISTS text_snippet_logs');
    }
}
