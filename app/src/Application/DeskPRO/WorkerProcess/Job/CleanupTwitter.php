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
 * @subpackage WorkerProcess
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\Log\Logger;

/**
 * This cleans up various temporary data
 */
class CleanupTwitter extends AbstractJob
{
	const DEFAULT_INTERVAL = 3600; // hourly

	public function run()
	{
		$db = App::getDb();
		$cutoff = gmdate('Y-m-d H:i:s', time() - App::getSetting('core.twitter_auto_remove_time'));

		$db->executeUpdate("
			DELETE IGNORE FROM twitter_accounts_statuses
			WHERE (status_type = 'timeline' OR status_type IS NULL)
				AND is_favorited = 0
				AND agent_id IS NULL
				AND agent_team_id IS NULL
				AND retweeted_id IS NULL
				AND action_agent_id IS NULL
				AND date_created < ?
		", array($cutoff));

		$deleted = $db->executeUpdate("
			DELETE IGNORE s FROM twitter_statuses AS s
			LEFT JOIN twitter_accounts_statuses AS accs ON (s.id = accs.status_id)
			WHERE s.date_created < ?
				AND accs.id IS NULL
		", array($cutoff));

		if ($deleted) {
			$this->logStatus("Cleaned up $deleted statuses");
		}
	}
}
