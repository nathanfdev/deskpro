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

class Build1517843483 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE saved_dashboard_widget (id INT AUTO_INCREMENT NOT NULL, dashboard_widget_id INT DEFAULT NULL, saved_report_id INT NOT NULL, title VARCHAR(255) NOT NULL, position VARCHAR(5) NOT NULL, size VARCHAR(5) NOT NULL, type VARCHAR(50) DEFAULT NULL, variables LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', options LONGTEXT DEFAULT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', INDEX IDX_7EEC518DB31FDD11 (dashboard_widget_id), INDEX IDX_7EEC518D7E09ED3D (saved_report_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE saved_dashboard_report (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, columns INT DEFAULT 24 NOT NULL, variables LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', date_created DATETIME DEFAULT NULL, authcode VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->execDbQuery('default', 'ALTER TABLE saved_dashboard_widget ADD CONSTRAINT FK_7EEC518DB31FDD11 FOREIGN KEY (dashboard_widget_id) REFERENCES report_widget (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE saved_dashboard_widget ADD CONSTRAINT FK_7EEC518D7E09ED3D FOREIGN KEY (saved_report_id) REFERENCES saved_dashboard_report (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
