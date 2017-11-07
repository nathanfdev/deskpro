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
