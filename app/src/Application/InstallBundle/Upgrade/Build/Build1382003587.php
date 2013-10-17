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

class Build1382003587 extends AbstractBuild
{
	public function run()
	{
		$this->out("Add templates.ticket_trigger");
		$this->execMutateSql("ALTER TABLE templates ADD ticket_trigger_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE templates ADD CONSTRAINT FK_6F287D8EC94E4E82 FOREIGN KEY (ticket_trigger_id) REFERENCES ticket_triggers (id) ON DELETE CASCADE");
		$this->execMutateSql("CREATE INDEX IDX_6F287D8EC94E4E82 ON templates (ticket_trigger_id)");

		$this->out("Add ticket_triggers.department_id, ticket_triggers.email_gateway_id");
		$this->execMutateSql("ALTER TABLE ticket_triggers ADD department_id INT DEFAULT NULL, ADD email_gateway_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE ticket_triggers ADD CONSTRAINT FK_8BA775CCAE80F5DF FOREIGN KEY (department_id) REFERENCES departments (id) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE ticket_triggers ADD CONSTRAINT FK_8BA775CCFBCC7CDF FOREIGN KEY (email_gateway_id) REFERENCES email_gateways (id) ON DELETE CASCADE");
		$this->execMutateSql("CREATE INDEX IDX_8BA775CCAE80F5DF ON ticket_triggers (department_id)");
		$this->execMutateSql("CREATE INDEX IDX_8BA775CCFBCC7CDF ON ticket_triggers (email_gateway_id)");
	}
}