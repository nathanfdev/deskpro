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

class Build1409758642 extends AbstractBuild
{
	public function run()
	{
		$this->out("Upgrade usersources to new auth settings");
		$this->execMutateSql("ALTER TABLE usersources ADD display_order_user INT NOT NULL, ADD display_order_agent INT NOT NULL, ADD is_enabled_user TINYINT(1) NOT NULL, ADD is_enabled_agent TINYINT(1) NOT NULL");
		$this->execMutateSql("
			UPDATE usersources
			SET display_order_user = display_order + 1,
				display_order_agent = display_order + 1,
				is_enabled_user = is_enabled,
				is_enabled_agent = is_enabled
 		");

		$enable = $this->container->getSetting('core.deskpro_source_enabled') ? 1 : 0;
		$this->execMutateSql(
			"INSERT INTO usersources (title, source_type, lost_password_url, `options`, display_order, is_enabled, display_order_user, display_order_agent, is_enabled_user, is_enabled_agent) VALUES ('DeskPRO', 'Application\\\\DeskPRO\\\\Usersource\\\\Adapter\\\\DeskPRO', '/login/reset-password', '', 0, $enable, 0, 0, $enable, $enable)"
		);
	}
}
