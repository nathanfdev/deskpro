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

class Build1400056702 extends AbstractBuild
{
	public function run()
	{
		$db = $this->container->getDb();

		$this->out("Updates to api_keys and api_token table");

		$db->exec("ALTER TABLE api_keys ADD flags LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)'");

		$db->exec("DROP TABLE IF EXISTS api_token_rate_limit");
		$db->exec("DROP TABLE IF EXISTS api_token");

		$db->exec("CREATE TABLE api_token (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, token VARCHAR(25) NOT NULL, scope VARCHAR(50) NOT NULL, date_expires DATETIME DEFAULT NULL, INDEX IDX_7BA2F5EB217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$db->exec("CREATE TABLE api_token_rate_limit (api_token_id INT NOT NULL, hits INT DEFAULT NULL, created_stamp INT DEFAULT NULL, reset_stamp INT DEFAULT NULL, PRIMARY KEY(api_token_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$db->exec("ALTER TABLE api_token ADD CONSTRAINT FK_7BA2F5EB217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE");
		$db->exec("ALTER TABLE api_token_rate_limit ADD CONSTRAINT FK_458445A992E52D36 FOREIGN KEY (api_token_id) REFERENCES api_token (id) ON DELETE CASCADE");
	}
}