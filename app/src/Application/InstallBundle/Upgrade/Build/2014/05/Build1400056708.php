<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

class Build1400056708 extends AbstractBuild
{
	public function run()
	{
		$db = $this->container->getDb();

		$this->out("Create app tables");
		$db->exec("CREATE TABLE app_assets (id INT AUTO_INCREMENT NOT NULL, package_name VARCHAR(255) DEFAULT NULL, blob_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, tag VARCHAR(50) DEFAULT NULL, metadata LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', INDEX IDX_9CD2100EE56E1BCE (package_name), UNIQUE INDEX UNIQ_9CD2100EED3E8EA5 (blob_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$db->exec("CREATE TABLE app_instances (id INT AUTO_INCREMENT NOT NULL, package_name VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, secret_key VARCHAR(40) NOT NULL, auth_key VARCHAR(40) NOT NULL, settings LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', date_created DATETIME NOT NULL, INDEX IDX_D2485F5CE56E1BCE (package_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$db->exec("CREATE TABLE app_packages (name VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(1000) NOT NULL, author_name VARCHAR(255) NOT NULL, author_email VARCHAR(255) NOT NULL, author_link VARCHAR(255) NOT NULL, api_version INT NOT NULL, version INT NOT NULL, version_name VARCHAR(100) NOT NULL, native_name VARCHAR(255) DEFAULT NULL, is_single TINYINT(1) NOT NULL, is_custom TINYINT(1) NOT NULL, tags LONGTEXT NOT NULL COMMENT '(DC2Type:simple_array)', settings_def LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', scopes LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)', PRIMARY KEY(name)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$db->exec("ALTER TABLE app_assets ADD CONSTRAINT FK_9CD2100EE56E1BCE FOREIGN KEY (package_name) REFERENCES app_packages (name) ON DELETE CASCADE");
		$db->exec("ALTER TABLE app_assets ADD CONSTRAINT FK_9CD2100EED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE CASCADE");
		$db->exec("ALTER TABLE app_instances ADD CONSTRAINT FK_D2485F5CE56E1BCE FOREIGN KEY (package_name) REFERENCES app_packages (name) ON DELETE CASCADE");

		$this->out("Add app index on ticket_actions_def");
		$db->exec("ALTER TABLE ticket_actions_def ADD CONSTRAINT FK_5FEF87EF7987212D FOREIGN KEY (app_id) REFERENCES app_instances (id) ON DELETE CASCADE");
	}
}