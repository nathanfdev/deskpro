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

use Application\DeskPRO\App\Native\NativeAppsSync;
use Application\DeskPRO\App\Package\PackageInstaller;

class Build1400056729 extends AbstractBuild
{
	public function run()
	{
		$this->out("Sync apps");

		$did_do = $this->container->getDb()->fetchColumn("SELECT data FROM install_data WHERE build = 1413803749 AND name = 'did_pre_alter'");
		if (!$did_do) {
			$this->out("Adding auth fields");
			$this->execMutateSql("ALTER TABLE usersources  ADD type VARCHAR(25) NOT NULL, ADD is_sso_auto TINYINT(1) NOT NULL, ADD is_sso_background TINYINT(1) NOT NULL");
			$this->execMutateSql("ALTER TABLE usersources ADD agent_permission_group_id INT DEFAULT NULL, ADD auto_agent TINYINT(1) NOT NULL");
			$this->execMutateSql("ALTER TABLE usersources ADD CONSTRAINT FK_4E3C994CF9C72B85 FOREIGN KEY (agent_permission_group_id) REFERENCES usergroups (id) ON DELETE SET NULL");
			$this->execMutateSql("CREATE INDEX IDX_4E3C994CF9C72B85 ON usersources (agent_permission_group_id)");
			$this->execMutateSql("ALTER TABLE blobs ADD storage_loc_specific VARCHAR(50) DEFAULT NULL");
			$this->container->getDb()->insertIgnore('install_data', array('build' => '1413803749', 'name' => 'did_pre_alter', 'data' => '1'));
		}

		$app_syncer = new NativeAppsSync(
			$this->container,
			$this->container->getAppManager(),
			new PackageInstaller($this->container->getEm(), $this->container->getBlobStorage(), $this->container->getImagine()),
			null
		);

		$app_syncer->runUpdates();
		$app_syncer->runSync();
	}
}