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

class Build1420543837 extends AbstractBuild
{
    public function run()
    {
        $this->out("Add app permissions");
		$this->execMutateSql("CREATE TABLE app_instance_permissions (id INT AUTO_INCREMENT NOT NULL, app_instance_id INT DEFAULT NULL, usergroup_id INT DEFAULT NULL, person_id INT DEFAULT NULL, INDEX IDX_2B1F184E63B454A1 (app_instance_id), INDEX IDX_2B1F184ED2112630 (usergroup_id), INDEX IDX_2B1F184E217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("ALTER TABLE app_instance_permissions ADD CONSTRAINT FK_2B1F184E63B454A1 FOREIGN KEY (app_instance_id) REFERENCES app_instances (id) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE app_instance_permissions ADD CONSTRAINT FK_2B1F184ED2112630 FOREIGN KEY (usergroup_id) REFERENCES usergroups (id) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE app_instance_permissions ADD CONSTRAINT FK_2B1F184E217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE app_instances ADD perm_type VARCHAR(15) NOT NULL", true);
		$this->execMutateSql("UPDATE app_instances SET perm_type = 'global' WHERE perm_type = ''");
    }
}