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

class Build1400056707 extends AbstractBuild
{
	public function run()
	{
		$db = $this->container->getDb();

		$this->out("Create ticket_escalations table");
		$db->exec("CREATE TABLE ticket_escalations (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, event_trigger VARCHAR(50) NOT NULL, event_trigger_time INT NOT NULL, is_enabled TINYINT(1) NOT NULL, terms LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', terms_any LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', actions LONGTEXT NOT NULL COMMENT '(DC2Type:dp_json_obj)', date_created DATETIME NOT NULL, date_last_run DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");

		$this->out("Create ticket_escalation_logs table");
		$db->exec("CREATE TABLE ticket_escalation_logs (id INT AUTO_INCREMENT NOT NULL, ticket_id INT DEFAULT NULL, escalation_id INT DEFAULT NULL, date_ran DATETIME NOT NULL, date_criteria DATETIME NOT NULL, INDEX IDX_10B6C273700047D2 (ticket_id), INDEX IDX_10B6C273703EE70D (escalation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$db->exec("ALTER TABLE ticket_escalation_logs ADD CONSTRAINT FK_10B6C273700047D2 FOREIGN KEY (ticket_id) REFERENCES tickets (id) ON DELETE CASCADE");
		$db->exec("ALTER TABLE ticket_escalation_logs ADD CONSTRAINT FK_10B6C273703EE70D FOREIGN KEY (escalation_id) REFERENCES ticket_escalations (id) ON DELETE CASCADE");

		$this->out("Adding field tickets_logs.escalation_id");
		$db->exec("ALTER TABLE tickets_logs ADD escalation_id INT DEFAULT NULL");
	}
}