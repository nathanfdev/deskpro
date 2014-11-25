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

class Build1416907908 extends AbstractBuild
{
	public function run()
	{
		$this->out("Upgrade JIRA Issues");
		$this->execMutateSql("ALTER TABLE jira_issues DROP FOREIGN KEY FK_88385CE2700047D2");
		$this->execMutateSql("ALTER TABLE jira_issues ADD status_id INT DEFAULT NULL, DROP last_synced, CHANGE created created DATETIME NOT NULL");
		$this->execMutateSql("ALTER TABLE jira_issues ADD CONSTRAINT FK_88385CE2700047D2 FOREIGN KEY (ticket_id) REFERENCES tickets (id) ON DELETE CASCADE");
		$this->execMutateSql("DELETE FROM app_packages WHERE name = 'deskpro_jira'");
		$this->execMutateSql("DROP TABLE IF EXISTS `jira_issue_comments`");
	}
}