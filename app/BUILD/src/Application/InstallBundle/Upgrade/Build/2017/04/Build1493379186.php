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

class Build1493379186 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'DROP TABLE notify_action_alerts;');
        $this->execDbQuery('default', 'DROP TABLE notify_notifications;');
        $this->execDbQuery('default', 'CREATE TABLE notify_action_alerts (id BIGINT AUTO_INCREMENT NOT NULL, target_id INT NOT NULL, uuid VARCHAR(80) NOT NULL, date_created DATETIME NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', type VARCHAR(100) NOT NULL, INDEX target_id (target_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE notify_notifications (id BIGINT AUTO_INCREMENT NOT NULL, target_id INT NOT NULL, uuid VARCHAR(80) NOT NULL, date_created DATETIME NOT NULL, is_dismissed TINYINT(1) DEFAULT \'0\' NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', type VARCHAR(100) NOT NULL, INDEX target_id (target_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
