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

class Build1391700332 extends AbstractBuild
{
	public function run()
	{
		$this->execMutateSql("DROP TABLE IF EXISTS ticket_trigger_plugin_actions;");
		$this->execMutateSql("CREATE TABLE ticket_actions_def (id INT AUTO_INCREMENT NOT NULL, app_id INT DEFAULT NULL, event_type VARCHAR(50) NOT NULL, def_class VARCHAR(255) DEFAULT NULL, settings LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', INDEX IDX_5FEF87EF7987212D (app_id), UNIQUE INDEX event_type_idx (event_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("ALTER TABLE ticket_actions_def ADD CONSTRAINT FK_5FEF87EF7987212D FOREIGN KEY (app_id) REFERENCES app_instances (id) ON DELETE CASCADE");
	}
}