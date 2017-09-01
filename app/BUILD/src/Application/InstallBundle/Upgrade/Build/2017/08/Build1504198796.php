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

// NOTE: I used the BlockingBuildInterface interface because
//       it looks like your schema changes are NOT backwards compatible with the previous version.
//       You should double-check this yourself though. If they are backwards compatible, use OnlineBuildInterface instead.

// Please remove these NOTE comments after you have checked the code.

class Build1504198796 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', "CREATE TABLE ticket_webhooks (id INT AUTO_INCREMENT NOT NULL, app_instance_id INT DEFAULT NULL, auth_id VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, payload_decoder VARCHAR(255) NOT NULL, is_enabled TINYINT(1) NOT NULL, search_terms LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', actions LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', INDEX IDX_1CE5B35C63B454A1 (app_instance_id), UNIQUE INDEX auth_id_unique (auth_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->execDbQuery('default', 'ALTER TABLE ticket_webhooks ADD CONSTRAINT FK_1CE5B35C63B454A1 FOREIGN KEY (app_instance_id) REFERENCES app2_app_instance (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
