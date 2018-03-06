<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

namespace Application\InstallBundle\Upgrade\Build;

class Build1520331570 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE ticket_feedback_links (id INT AUTO_INCREMENT NOT NULL, ticket_id INT NOT NULL, feedback_id INT NOT NULL, person_id INT DEFAULT NULL, date_created DATETIME NOT NULL, INDEX IDX_B3C1CF38700047D2 (ticket_id), INDEX IDX_B3C1CF38D249A887 (feedback_id), INDEX IDX_B3C1CF38217BBB47 (person_id), UNIQUE INDEX ticket_feedback_links_unique (ticket_id, feedback_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE ticket_feedback_links ADD CONSTRAINT FK_B3C1CF38700047D2 FOREIGN KEY (ticket_id) REFERENCES tickets (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE ticket_feedback_links ADD CONSTRAINT FK_B3C1CF38D249A887 FOREIGN KEY (feedback_id) REFERENCES feedback (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE ticket_feedback_links ADD CONSTRAINT FK_B3C1CF38217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
