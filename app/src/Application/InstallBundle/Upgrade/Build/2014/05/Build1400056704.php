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

class Build1400056704 extends AbstractBuild
{
	public function run()
	{
		$db = $this->container->getDb();

		$this->out("Add sendmail_queue.status");
		$db->exec("ALTER TABLE sendmail_queue ADD status VARCHAR(15) NOT NULL");
		$db->exec("UPDATE sendmail_queue SET status = 'complete' WHERE has_sent = 1");
		$db->exec("UPDATE sendmail_queue SET status = 'error' WHERE has_sent = 0 AND date_next_attempt IS NULL");
		$db->exec("UPDATE sendmail_queue SET status = 'pending' WHERE has_sent = 0 AND date_next_attempt IS NOT NULL");

		$this->out("Correct email_sources.status");
		$db->exec("UPDATE email_sources SET status = 'rejected' WHERE status = 'error' AND error_code NOT IN ('server_error', 'timeout')");
	}
}