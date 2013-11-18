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

class Build1384788473 extends AbstractBuild
{
	public function run()
	{
		$this->out("Remake slas");

		//TODO
		$this->execMutateSql("DELETE FROM slas");
		$this->execMutateSql("DELETE FROM ticket_triggers WHERE event_trigger LIKE 'sla.%'");

		$this->execMutateSql("ALTER TABLE slas DROP FOREIGN KEY FK_ACE9984A13CC0145");
		$this->execMutateSql("ALTER TABLE slas DROP FOREIGN KEY FK_ACE9984A55EA90D4");
		$this->execMutateSql("ALTER TABLE slas DROP FOREIGN KEY FK_ACE9984A91D0B882");
		$this->execMutateSql("ALTER TABLE slas DROP FOREIGN KEY FK_ACE9984AED1A7B28");
		$this->execMutateSql("DROP INDEX IDX_ACE9984A91D0B882 ON slas");
		$this->execMutateSql("DROP INDEX IDX_ACE9984A55EA90D4 ON slas");
		$this->execMutateSql("DROP INDEX IDX_ACE9984A13CC0145 ON slas");
		$this->execMutateSql("DROP INDEX IDX_ACE9984AED1A7B28 ON slas");
		$this->execMutateSql("ALTER TABLE slas ADD apply_terms LONGBLOB NOT NULL COMMENT '(DC2Type:object)', ADD warn_time INT NOT NULL, ADD warn_time_unit VARCHAR(50) NOT NULL, ADD warn_actions LONGBLOB NOT NULL COMMENT '(DC2Type:object)', ADD fail_time INT NOT NULL, ADD fail_time_unit VARCHAR(50) NOT NULL, ADD fail_actions LONGBLOB NOT NULL COMMENT '(DC2Type:object)', DROP apply_priority_id, DROP fail_trigger_id, DROP warning_trigger_id, DROP apply_trigger_id");
	}
}