<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1510075772 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE ticket_follow_ups (id INT AUTO_INCREMENT NOT NULL, ticket_id INT DEFAULT NULL, person_id INT DEFAULT NULL, actions LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', cancel_if_user_reply TINYINT(1) NOT NULL, status VARCHAR(255) NOT NULL, date_created DATETIME NOT NULL, date_to_run DATETIME NOT NULL, date_did_run DATETIME DEFAULT NULL, INDEX IDX_745F3E6A700047D2 (ticket_id), INDEX IDX_745F3E6A217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE ticket_follow_ups ADD CONSTRAINT FK_745F3E6A700047D2 FOREIGN KEY (ticket_id) REFERENCES tickets (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE ticket_follow_ups ADD CONSTRAINT FK_745F3E6A217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'CREATE INDEX status_date_to_run ON ticket_follow_ups (status, date_to_run)');
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', 'INSERT INTO `worker_jobs` (`id`, `worker_group`, `title`, `description`, `job_class`, `data`, `run_interval`) VALUES (\'ticket_follow_ups\', \'ticket_follow_ups\', \'Ticket Follow Ups\', \'Basically like macros except they run on a schedule, e.g. automatically add a reply after 3 days.\', \'Application\\DeskPRO\\WorkerProcess\\Job\\TicketFollowUps\', \'a:0:{}\', \'60\');');
    }
}
