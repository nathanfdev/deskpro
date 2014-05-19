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

class Build1396876100 extends AbstractBuild
{
	public function run()
	{
		$db = $this->container->getDb();

		$this->out("Recreate ticket_triggers table");
		$db->exec("DROP TABLE IF EXISTS ticket_triggers");
		$db->exec("CREATE TABLE ticket_triggers (id INT AUTO_INCREMENT NOT NULL, department_id INT DEFAULT NULL, email_account_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, event_trigger VARCHAR(50) NOT NULL, event_flags LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)', by_agent_mode LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)', by_user_mode LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)', is_enabled TINYINT(1) NOT NULL, is_hidden TINYINT(1) NOT NULL, is_editable TINYINT(1) NOT NULL, sys_name VARCHAR(150) DEFAULT NULL, terms LONGTEXT NOT NULL COMMENT '(DC2Type:dp_json_obj)', actions LONGTEXT NOT NULL COMMENT '(DC2Type:dp_json_obj)', run_order INT NOT NULL, INDEX IDX_8BA775CCAE80F5DF (department_id), INDEX IDX_8BA775CC37D8AD65 (email_account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$db->exec("ALTER TABLE ticket_triggers ADD CONSTRAINT FK_8BA775CCAE80F5DF FOREIGN KEY (department_id) REFERENCES departments (id) ON DELETE CASCADE, ADD CONSTRAINT FK_8BA775CC37D8AD65 FOREIGN KEY (email_account_id) REFERENCES email_accounts (id) ON DELETE CASCADE");
	}
}