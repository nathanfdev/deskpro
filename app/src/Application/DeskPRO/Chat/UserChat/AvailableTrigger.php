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
 */

namespace Application\DeskPRO\Chat\UserChat;

use Application\DeskPRO\App;

class AvailableTrigger
{
	/**
	 * Update the chat status
	 *
	 * @param bool|null $is_chat_available True/false to mark chat as available/unavailable, null to auto-detect with query
	 */
	public static function update($is_chat_available = null)
	{
		if ($is_chat_available === null) {
			$is_chat_available = false;
			if (App::getSetting('core.apps_chat') && App::getOrm()->getRepository('DeskPRO:Session')->hasAvailableAgents(true)) {
				$is_chat_available = true;
			}
		}

		$is_chat_available = (bool)$is_chat_available;

		$trigger_File = dp_get_data_dir() . '/chat_is_available.trigger';
		if ($is_chat_available) {
			@file_put_contents($trigger_File, time());
		} elseif (is_file($trigger_File)) {
			@unlink($trigger_File);
		}
	}
}