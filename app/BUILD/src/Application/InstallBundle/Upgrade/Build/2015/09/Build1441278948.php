<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

class Build1441278948 extends AbstractBuild
{
    public function run()
    {
        $this->out('Problem Entity');
        $this->execMutateSql('CREATE TABLE problems (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, created DATETIME NOT NULL, is_open TINYINT(1) NOT NULL, INDEX IDX_8E666245217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execMutateSql('CREATE TABLE problem2tickets (problem_id INT NOT NULL, ticket_id INT NOT NULL, INDEX IDX_F98AE8EDA0DCED86 (problem_id), INDEX IDX_F98AE8ED700047D2 (ticket_id), PRIMARY KEY(ticket_id, problem_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execMutateSql('ALTER TABLE problems ADD CONSTRAINT FK_8E666245217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execMutateSql('ALTER TABLE problem2tickets ADD CONSTRAINT FK_F98AE8EDA0DCED86 FOREIGN KEY (problem_id) REFERENCES problems (id) ON DELETE CASCADE');
        $this->execMutateSql('ALTER TABLE problem2tickets ADD CONSTRAINT FK_F98AE8ED700047D2 FOREIGN KEY (ticket_id) REFERENCES tickets (id) ON DELETE CASCADE');
    }
}
