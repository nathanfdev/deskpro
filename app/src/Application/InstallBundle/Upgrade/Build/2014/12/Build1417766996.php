<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

class Build1417766996 extends AbstractBuild
{
    public function run()
    {
        $this->out("Creating report dashboards tables");
		$this->execMutateSql("CREATE TABLE report_dashboard (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, `default` TINYINT(1) DEFAULT '0' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("CREATE TABLE report_dashboard_report (id INT AUTO_INCREMENT NOT NULL, dashboard_id INT NOT NULL, title VARCHAR(255) NOT NULL, sort_order INT(1) UNSIGNED NOT NULL, columns INT NOT NULL, INDEX IDX_6EE5C64BB9D04D2B (dashboard_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("CREATE TABLE report_dashboard_widget (id INT AUTO_INCREMENT NOT NULL, widget_id INT NOT NULL, report_id INT NOT NULL, title VARCHAR(255) NOT NULL, position VARCHAR(5) NOT NULL, size VARCHAR(5) NOT NULL, INDEX IDX_2F33AF1FFBE885E2 (widget_id), INDEX IDX_2F33AF1F4BD2A4C0 (report_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("CREATE TABLE report_dashboard_permission (id INT AUTO_INCREMENT NOT NULL, dashboard_id INT DEFAULT NULL, person_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, INDEX IDX_DED8DEFB9D04D2B (dashboard_id), INDEX IDX_DED8DEF217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
		$this->execMutateSql("ALTER TABLE report_dashboard_report ADD CONSTRAINT FK_6EE5C64BB9D04D2B FOREIGN KEY (dashboard_id) REFERENCES report_dashboard (id) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE report_dashboard_widget ADD CONSTRAINT FK_2F33AF1FFBE885E2 FOREIGN KEY (widget_id) REFERENCES report_builder (id) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE report_dashboard_widget ADD CONSTRAINT FK_2F33AF1F4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report_dashboard_report (id) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE report_dashboard_permission ADD CONSTRAINT FK_DED8DEFB9D04D2B FOREIGN KEY (dashboard_id) REFERENCES report_dashboard (id) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE report_dashboard_permission ADD CONSTRAINT FK_DED8DEF217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE");
        /**
         * @todo Create predefined default widgets
         *
         */
    }
}