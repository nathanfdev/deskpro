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

class Build1413803740 extends AbstractBuild
{
	public function run()
	{
		$this->out("Adds a Facebook channel");
		$this->execMutateSql("CREATE TABLE facebook_apps (id INT AUTO_INCREMENT NOT NULL, app_id VARCHAR(256) DEFAULT NULL, app_secret VARCHAR(256) DEFAULT NULL, app_access_token VARCHAR(256) DEFAULT NULL, name VARCHAR(256) DEFAULT NULL, icon_url VARCHAR(256) DEFAULT NULL, logo_url VARCHAR(256) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("CREATE TABLE facebook_pages (id INT AUTO_INCREMENT NOT NULL, graph_id VARCHAR(256) DEFAULT NULL, page_token VARCHAR(256) DEFAULT NULL, user_graph_id VARCHAR(256) DEFAULT NULL, name VARCHAR(256) DEFAULT NULL, import_wall_posts TINYINT(1) NOT NULL, disable_own_wall_posts TINYINT(1) NOT NULL, import_direct_messages TINYINT(1) NOT NULL, is_enabled TINYINT(1) NOT NULL, is_connected TINYINT(1) NOT NULL, is_tested TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
	}
}