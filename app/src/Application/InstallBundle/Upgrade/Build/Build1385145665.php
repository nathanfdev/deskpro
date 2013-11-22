<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
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

class Build1385145665 extends AbstractBuild
{
	public function run()
	{
		$this->execMutateSql("CREATE TABLE plugin_packages (name VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, author_name VARCHAR(255) NOT NULL, author_email VARCHAR(255) NOT NULL, author_link VARCHAR(255) NOT NULL, api_version INT NOT NULL, version INT NOT NULL, version_name VARCHAR(100) NOT NULL, native_name VARCHAR(255) DEFAULT NULL, is_single TINYINT(1) NOT NULL, PRIMARY KEY(name)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("ALTER TABLE plugins DROP FOREIGN KEY FK_EC85F6718150E793");
		$this->execMutateSql("ALTER TABLE plugin_assets DROP FOREIGN KEY FK_CB93A328150E793, DROP INDEX IDX_CB93A328150E793");
		$this->execMutateSql("ALTER TABLE plugins ADD package_name VARCHAR(255) DEFAULT NULL, DROP plugin_def_id");
		$this->execMutateSql("ALTER TABLE plugins ADD CONSTRAINT FK_EC85F671E56E1BCE FOREIGN KEY (package_name) REFERENCES plugin_packages (name) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE plugin_assets ADD package_name VARCHAR(255) DEFAULT NULL, DROP plugin_def_id");
		$this->execMutateSql("ALTER TABLE plugin_assets ADD CONSTRAINT FK_CB93A32E56E1BCE FOREIGN KEY (package_name) REFERENCES plugin_packages (name) ON DELETE CASCADE");
		$this->execMutateSql("CREATE INDEX IDX_CB93A32E56E1BCE ON plugin_assets (package_name)");
		$this->execMutateSql("CREATE INDEX IDX_EC85F671E56E1BCE ON plugins (package_name)");
		$this->execMutateSql("DROP TABLE plugin_defs");
	}
}