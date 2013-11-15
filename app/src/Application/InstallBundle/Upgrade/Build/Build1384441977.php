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

class Build1384441977 extends AbstractBuild
{
	public function run()
	{
		$this->execMutateSql("CREATE TABLE log_request_stats (id INT AUTO_INCREMENT NOT NULL, date_created DATETIME NOT NULL, request_id VARCHAR(100) NOT NULL, user_agent VARCHAR(255) NOT NULL, user_ip VARCHAR(255) NOT NULL, page_id VARCHAR(255) NOT NULL, page_url VARCHAR(1000) NOT NULL, response_type VARCHAR(100) NOT NULL, response_code INT NOT NULL, response_size INT NOT NULL, query_count INT NOT NULL, time_php NUMERIC(8, 4) NOT NULL, time_db NUMERIC(8, 4) NOT NULL, time_end NUMERIC(8, 4) NOT NULL, time_userend NUMERIC(8, 4) NOT NULL, INDEX date_created_idx (date_created, request_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
	}
}