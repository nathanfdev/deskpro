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

class Build1407555184 extends AbstractBuild
{
	public function run()
	{
		$this->out("Create SMS account table");
		$this->execMutateSql("CREATE TABLE sms_accounts (id INT AUTO_INCREMENT NOT NULL, phone_number_id INT DEFAULT NULL, type VARCHAR(20) NOT NULL, params LONGBLOB DEFAULT NULL COMMENT '(DC2Type:array)', identifier VARCHAR(128) DEFAULT NULL, is_enabled TINYINT(1) NOT NULL, is_connected TINYINT(1) NOT NULL, is_tested TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_AC3EBFAD39DFD528 (phone_number_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("ALTER TABLE sms_accounts ADD CONSTRAINT FK_AC3EBFAD39DFD528 FOREIGN KEY (phone_number_id) REFERENCES phone_numbers (id) ON DELETE SET NULL");
	}
}
