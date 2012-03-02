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
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Orb\Util\Numbers;
use Orb\Util\Arrays;

use \Doctrine\ORM\EntityRepository;

class AgentNotification extends EntityRepository
{
	/**
	 * Fetch an array of notification subscriptions we have on a set of filters,
	 * and for a set of types.
	 *
	 * This is used after a change to figure out who wants notifications.
	 * The returned array is array(person_id=>array(notify type)). Each item
	 * is unique, so you don't have to worry about sending multiple notifications
	 * for the same event.
	 *
	 * @return array
	 */
	public function getNotifications(array $matching_filters, array $notify_types)
	{
		if (!$notify_types) {
			return array();
		}

		$db = App::getDb();
		$matching_filters = array_filter($matching_filters, function ($val) {
			if (Numbers::isInteger($val)) {
				return true;
			}
			return false;
		});

		$matching_filters = Arrays::removeFalsey($matching_filters);
		$matching_filters = array_unique($matching_filters);
		$matching_filters = implode(',', $matching_filters);

		$notify_types = $db->quoteIn($notify_types);

		$notifs = $db->fetchAllGrouped("
			SELECT person_id, notify_type
			FROM agent_notifications
			WHERE filter_id IN ($matching_filters) AND notify_type IN ($notify_types)
			GROUP BY person_id
		", array(), 'person_id', null, 'notify_type');

		return $notifs;
	}
}
