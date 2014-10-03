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

class Build1411577850 extends AbstractBuild
{
	public function run()
	{
		$this->out("Adding avatar feilds to temas and departments");
		$this->execMutateSql("ALTER TABLE agent_teams ADD avatar_blob_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE agent_teams ADD CONSTRAINT FK_AF6C0A203B50817B FOREIGN KEY (avatar_blob_id) REFERENCES blobs (id) ON DELETE CASCADE");
		$this->execMutateSql("CREATE INDEX IDX_AF6C0A203B50817B ON agent_teams (avatar_blob_id)");
		$this->execMutateSql("ALTER TABLE departments ADD avatar_blob_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE departments ADD CONSTRAINT FK_16AEB8D43B50817B FOREIGN KEY (avatar_blob_id) REFERENCES blobs (id) ON DELETE CASCADE");
		$this->execMutateSql("CREATE INDEX IDX_16AEB8D43B50817B ON departments (avatar_blob_id)");
	}
}