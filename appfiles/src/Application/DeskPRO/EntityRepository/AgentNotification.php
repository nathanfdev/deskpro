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
	 * Fetch an array of notification subscriptions we have on a set of queues,
	 * and for a set of types.
	 *
	 * This is used after a change to figure out who wants notifications.
	 * The returned array is array(person_id=>array(notify type)). Each item
	 * is unique, so you don't have to worry about sending multiple notifications
	 * for the same event.
	 *
	 * @return array
	 */
	public function getNotifications(array $matching_queues, array $notify_types)
	{
		if (!$notify_types) {
			return array();
		}

		$db = App::getDb();
		$matching_queues = array_filter($matching_queues, function ($val) {
			if (Numbers::isInteger($val)) {
				return true;
			}
			return false;
		});

		$matching_queues = Arrays::removeFalsey($matching_queues);
		$matching_queues = array_unique($matching_queues);
		$matching_queues = implode(',', $matching_queues);

		$notify_types = $db->quoteIn($notify_types);

		$notifs = $db->fetchAllGrouped("
			SELECT person_id, notify_type
			FROM agent_notifications
			WHERE queue_id IN ($matching_queues) AND notify_type IN ($notify_types)
			GROUP BY person_id
		", array(), 'person_id', null, 'notify_type');

		return $notifs;
	}
}