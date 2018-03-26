<?php

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
