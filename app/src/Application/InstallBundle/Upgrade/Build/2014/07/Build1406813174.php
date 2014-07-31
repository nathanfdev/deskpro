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

class Build1406813174 extends AbstractBuild
{
	public function run()
	{
		$this->out("My Upgrade Class");
		$this->execMutateSql("CREATE TABLE log_event (id INT UNSIGNED AUTO_INCREMENT NOT NULL, parent_id INT UNSIGNED DEFAULT NULL, person_id INT DEFAULT NULL, timestamp INT UNSIGNED NOT NULL, event VARCHAR(255) NOT NULL, subject VARCHAR(255) DEFAULT NULL, subject_id INT UNSIGNED DEFAULT NULL, details LONGBLOB NOT NULL COMMENT '(DC2Type:array)', INDEX IDX_1BD0E1FA727ACA70 (parent_id), INDEX IDX_1BD0E1FA217BBB47 (person_id), INDEX subject (subject, subject_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("ALTER TABLE log_event ADD CONSTRAINT FK_1BD0E1FA727ACA70 FOREIGN KEY (parent_id) REFERENCES log_event (id) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE log_event ADD CONSTRAINT FK_1BD0E1FA217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE");
	}
}