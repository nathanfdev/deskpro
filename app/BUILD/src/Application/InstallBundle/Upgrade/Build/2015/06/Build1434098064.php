<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

class Build1434098064 extends AbstractBuild
{
    public function run()
    {
        $this->out('add usersource sync');
        $this->execMutateSql('CREATE TABLE usersource_sync_log (id INT AUTO_INCREMENT NOT NULL, usersource_id INT DEFAULT NULL, record_count INT NOT NULL, date_start DATETIME DEFAULT NULL, date_end DATETIME DEFAULT NULL, date_phase_2_start DATETIME DEFAULT NULL, date_phase_2_end DATETIME DEFAULT NULL, status VARCHAR(256) DEFAULT NULL, INDEX IDX_C5ADA5725B71BD01 (usersource_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execMutateSql('ALTER TABLE usersource_sync_log ADD CONSTRAINT FK_C5ADA5725B71BD01 FOREIGN KEY (usersource_id) REFERENCES usersources (id)');
        $this->execMutateSql('ALTER TABLE person_usersource_assoc ADD updated_at DATETIME DEFAULT NULL');

        // true param means to ignore dupe errors because this might have been added before in the big 322 update (Build1400056701)
        $this->execMutateSql("ALTER TABLE usersources ADD sync_enabled TINYINT(1) DEFAULT '0' NOT NULL", true);
    }
}
