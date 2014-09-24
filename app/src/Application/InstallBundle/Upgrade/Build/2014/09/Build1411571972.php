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

class Build1411571972 extends AbstractBuild
{
	public function run()
	{
		$this->out("Primary Team upgrade");
		$this->execMutateSql(
			<<<SQL
			ALTER TABLE agent_team_members DROP FOREIGN KEY FK_CC952C03217BBB47;
ALTER TABLE agent_team_members DROP FOREIGN KEY FK_CC952C03296CD8AE;
ALTER TABLE agent_team_members ADD CONSTRAINT FK_CC952C03217BBB47 FOREIGN KEY (person_id) REFERENCES people (id);
ALTER TABLE agent_team_members ADD CONSTRAINT FK_CC952C03296CD8AE FOREIGN KEY (team_id) REFERENCES agent_teams (id);
ALTER TABLE people ADD primary_team_id INT DEFAULT NULL;
ALTER TABLE people ADD CONSTRAINT FK_28166A26E715BE01 FOREIGN KEY (primary_team_id) REFERENCES agent_teams (id);
CREATE INDEX IDX_28166A26E715BE01 ON people (primary_team_id);
SQL
		);
	}
}