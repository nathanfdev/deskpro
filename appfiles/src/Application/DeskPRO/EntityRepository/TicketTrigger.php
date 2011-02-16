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

class TicketTrigger extends EntityRepository
{
	/**
	 * Find all triggers that should be run/tested for a given event type.
	 *
	 * @return array
	 */
	public function getTriggersForEvents(array $events)
	{
		if (!$events) {
			return array();
		}

		$db = App::getDb();
		$events = $db->quoteIn($v);

		$events = $this->getEntityManager()->createQuery("
			SELECT trig
			FROM DeskPRO:TicketTrigger trig
			WHERE trig.event_trigger IN ($events)
			AND trig.is_enabled = true
		")->execute();

		return $triggers;
	}
}