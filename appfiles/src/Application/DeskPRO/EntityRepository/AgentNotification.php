<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EntityRepository;

use \Application\DeskPRO\App;
use \Orb\Util\Numbers;
use \Orb\Util\Arrays;

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