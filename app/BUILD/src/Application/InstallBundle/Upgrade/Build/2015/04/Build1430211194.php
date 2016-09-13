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

class Build1430211194 extends AbstractBuild
{
    public function run()
    {
        $this->out('Mail Tracking');
        $this->execMutateSql('CREATE TABLE sendmail_source_statuses (id INT AUTO_INCREMENT NOT NULL, sendmail_source_id INT DEFAULT NULL, user_email VARCHAR(255) NOT NULL, event_type VARCHAR(255) NOT NULL, event_info VARCHAR(255) NOT NULL, details LONGTEXT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_7DB3604F2E621D8C (sendmail_source_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execMutateSql('ALTER TABLE sendmail_source_statuses ADD CONSTRAINT FK_7DB3604F2E621D8C FOREIGN KEY (sendmail_source_id) REFERENCES sendmail_sources (id) ON DELETE CASCADE');
        $this->execMutateSql('ALTER TABLE sendmail_sources ADD num_targets INT NOT NULL, ADD num_pending INT NOT NULL, ADD num_error INT NOT NULL, ADD num_complete INT NOT NULL');
    }
}
