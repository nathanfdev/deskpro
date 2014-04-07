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

class Build1396876085 extends AbstractBuild
{
	public function run()
	{
		$db = $this->container->getDb();

		$this->out("Convert email_uids.gateway_id -> email_uids.email_account_id");
		$db->exec("ALTER TABLE email_uids DROP FOREIGN KEY FK_6D08D1BD577F8E00");
		$db->exec("ALTER TABLE email_uids DROP KEY IDX_6D08D1BD577F8E00");
		$db->exec("ALTER TABLE email_uids CHANGE gateway_id email_account_id INT DEFAULT NULL");
		$db->exec("SET FOREIGN_KEY_CHECKS = 0");
		$db->exec("ALTER TABLE email_uids ADD CONSTRAINT FK_6D08D1BD37D8AD65 FOREIGN KEY (email_account_id) REFERENCES email_accounts (id) ON DELETE CASCADE");
		$db->exec("CREATE INDEX IDX_6D08D1BD37D8AD65 ON email_uids (email_account_id)");
		$db->exec("SET FOREIGN_KEY_CHECKS = 1");
	}
}