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

class Build1394823636 extends AbstractBuild
{
	public function run()
	{
		$this->execMutateSql("ALTER TABLE email_sources DROP FOREIGN KEY FK_6F9D0D3D577F8E00");
		$this->execMutateSql("DROP INDEX IDX_6F9D0D3D577F8E00 ON email_sources");
		$this->execMutateSql("ALTER TABLE email_sources CHANGE gateway_id email_account_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE email_sources ADD CONSTRAINT FK_6F9D0D3D37D8AD65 FOREIGN KEY (email_account_id) REFERENCES email_accounts (id) ON DELETE CASCADE");
		$this->execMutateSql("CREATE INDEX IDX_6F9D0D3D37D8AD65 ON email_sources (email_account_id)");

		$this->execMutateSql("ALTER TABLE email_uids DROP FOREIGN KEY FK_6D08D1BD577F8E00");
		$this->execMutateSql("DROP INDEX IDX_6D08D1BD577F8E00 ON email_uids");
		$this->execMutateSql("ALTER TABLE email_uids CHANGE id id VARCHAR(100) NOT NULL, CHANGE gateway_id email_account_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE email_uids ADD CONSTRAINT FK_6D08D1BD37D8AD65 FOREIGN KEY (email_account_id) REFERENCES email_accounts (id) ON DELETE CASCADE");
		$this->execMutateSql("CREATE INDEX IDX_6D08D1BD37D8AD65 ON email_uids (email_account_id)");

		$this->execMutateSql("ALTER TABLE ticket_triggers DROP FOREIGN KEY FK_8BA775CCFBCC7CDF");
		$this->execMutateSql("DROP INDEX IDX_8BA775CCFBCC7CDF ON ticket_triggers");
		$this->execMutateSql("ALTER TABLE ticket_triggers CHANGE email_gateway_id email_account_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE ticket_triggers ADD CONSTRAINT FK_8BA775CC37D8AD65 FOREIGN KEY (email_account_id) REFERENCES email_accounts (id) ON DELETE CASCADE");
		$this->execMutateSql("CREATE INDEX IDX_8BA775CC37D8AD65 ON ticket_triggers (email_account_id)");
	}
}