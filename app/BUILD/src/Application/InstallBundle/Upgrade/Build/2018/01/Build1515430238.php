<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

class Build1515430238 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE scheduled_reports (id BIGINT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, report_id INT DEFAULT NULL, when_setting LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', when_tz VARCHAR(32) NOT NULL, frequency VARCHAR(64) NOT NULL, next_send_date DATETIME DEFAULT NULL, INDEX IDX_95D8F101217BBB47 (person_id), INDEX IDX_95D8F1014BD2A4C0 (report_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE scheduled_reports ADD CONSTRAINT FK_95D8F101217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE scheduled_reports ADD CONSTRAINT FK_95D8F1014BD2A4C0 FOREIGN KEY (report_id) REFERENCES report_dashboard_report (id) ON DELETE CASCADE');
    }

    public function run()
    {
    }
}
