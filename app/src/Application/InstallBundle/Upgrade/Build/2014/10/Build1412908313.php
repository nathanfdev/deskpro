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

class Build1412908313 extends AbstractBuild
{
	public function run()
	{
		$this->out("New Custom Fields");
		$this->execMutateSql("CREATE TABLE custom_field_data (id INT AUTO_INCREMENT NOT NULL, definition_id INT DEFAULT NULL, root_definition_id INT DEFAULT NULL, owner_id INT NOT NULL, value INT NOT NULL, input LONGTEXT NOT NULL, INDEX IDX_81C0A673D11EA911 (definition_id), INDEX IDX_81C0A6738ABE91D7 (root_definition_id), UNIQUE INDEX unique_idx (owner_id, definition_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("CREATE TABLE custom_field_definition (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, app_id INT DEFAULT NULL, js_class VARCHAR(255) NOT NULL, has_form_template TINYINT(1) NOT NULL, has_display_template TINYINT(1) NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, options LONGBLOB NOT NULL COMMENT '(DC2Type:array)', default_value VARCHAR(500) DEFAULT NULL, display_order INT NOT NULL, is_enabled TINYINT(1) NOT NULL, is_user_enabled TINYINT(1) NOT NULL, is_agent_field TINYINT(1) NOT NULL, form_type VARCHAR(255) NOT NULL, owner_class VARCHAR(255) NOT NULL, context_class VARCHAR(255) DEFAULT NULL, context_id INT DEFAULT NULL, INDEX IDX_48DC8533727ACA70 (parent_id), INDEX IDX_48DC85337987212D (app_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("ALTER TABLE custom_field_data ADD CONSTRAINT FK_81C0A673D11EA911 FOREIGN KEY (definition_id) REFERENCES custom_field_definition (id) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE custom_field_data ADD CONSTRAINT FK_81C0A6738ABE91D7 FOREIGN KEY (root_definition_id) REFERENCES custom_field_definition (id) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE custom_field_definition ADD CONSTRAINT FK_48DC8533727ACA70 FOREIGN KEY (parent_id) REFERENCES custom_field_definition (id) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE custom_field_definition ADD CONSTRAINT FK_48DC85337987212D FOREIGN KEY (app_id) REFERENCES app_instances (id) ON DELETE SET NULL");
	}
}