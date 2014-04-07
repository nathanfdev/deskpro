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
		$db->exec("
			CREATE TABLE `ticket_triggers` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`department_id` int(11) DEFAULT NULL,
			`email_account_id` int(11) DEFAULT NULL,
			`title` varchar(255) NOT NULL,
			`event_trigger` varchar(50) NOT NULL,
			`by_agent_mode` longtext COMMENT '(DC2Type:simple_array)',
			`by_user_mode` longtext COMMENT '(DC2Type:simple_array)',
			`is_enabled` tinyint(1) NOT NULL,
			`terms` longtext NOT NULL COMMENT '(DC2Type:dp_json_obj)',
			`actions` longtext NOT NULL COMMENT '(DC2Type:dp_json_obj)',
			`run_order` int(11) NOT NULL,
			`is_hidden` tinyint(1) NOT NULL,
			`is_editable` tinyint(1) NOT NULL,
			`sys_name` varchar(150) DEFAULT NULL,
			PRIMARY KEY (`id`),
			KEY `IDX_8BA775CCAE80F5DF` (`department_id`),
			KEY `IDX_8BA775CC37D8AD65` (`email_account_id`),
			CONSTRAINT `FK_8BA775CC37D8AD65` FOREIGN KEY (`email_account_id`) REFERENCES `email_accounts` (`id`) ON DELETE CASCADE,
			CONSTRAINT `FK_8BA775CCAE80F5DF` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
	}
}