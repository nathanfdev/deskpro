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

namespace Application\InstallBundle\Upgrade\Build;

class Build1454850311 extends AbstractBuild
{
    public function run()
    {
        $this->out('New api log table');
        $this->execMutateSql('CREATE TABLE api_log (id INT AUTO_INCREMENT NOT NULL, api_key_id INT DEFAULT NULL, start_time INT NOT NULL, end_time INT NOT NULL, requested_uri VARCHAR(255) NOT NULL, status INT NOT NULL, request_data LONGTEXT NOT NULL, response_data LONGTEXT NOT NULL, INDEX IDX_CCBD2EF18BE312B3 (api_key_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execMutateSql('ALTER TABLE api_log ADD CONSTRAINT FK_CCBD2EF18BE312B3 FOREIGN KEY (api_key_id) REFERENCES api_keys (id) ON DELETE CASCADE');
    }
}
