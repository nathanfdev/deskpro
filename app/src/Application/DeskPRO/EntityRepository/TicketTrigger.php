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

class TicketTrigger extends AbstractEntityRepository
{
	/**
	 * @param string $event_type
	 * @return \Application\DeskPRO\Entity\TicketTrigger[]
	 */
	public function getTriggersForEventType($event_type)
	{
		$triggers = $this->_em->createQuery("
			SELECT t
			FROM DeskPRO:TicketTrigger t
			WHERE t.event_trigger = :event_type AND t.is_enabled = true
			ORDER BY t.run_order
		")->execute(array('event_type' => $event_type));

		return $triggers;
	}

	/**
	 * @param string|null $type
	 * @return \Application\DeskPRO\Entity\TicketTrigger[]
	 */
	public function getTriggers($type = null)
	{
		if ($type) {
			return $this->getEntityManager()->createQuery("
				SELECT t
				FROM DeskPRO:TicketTrigger t
				WHERE t.event_trigger = ?0
				ORDER BY t.run_order, t.title ASC
			")->execute(array($type));
		} else {
			return $this->getEntityManager()->createQuery("
				SELECT t
				FROM DeskPRO:TicketTrigger t
				ORDER BY t.run_order, t.title ASC
			")->execute();
		}
	}


	/**
	 * @param array $run_orders
	 */
	public function updateRunOrders(array $run_orders)
	{
		$run_orders = array_values($run_orders);

		$db = $this->_em->getConnection();
		$db->beginTransaction();

		$x = 0;
		foreach ($run_orders as $tr_id) {
			$x += 10;
			if ($tr_id == 'departments') {
				$db->executeUpdate("
					UPDATE ticket_triggers
					SET run_order = ?
					WHERE department_id IS NOT NULL AND event_trigger = 'newticket'
				", array($x));
			} else if ($tr_id == 'departments_changed') {
				$db->executeUpdate("
					UPDATE ticket_triggers
					SET run_order = ?
					WHERE department_id IS NOT NULL AND event_trigger = 'update'
				", array($x));
			} else if ($tr_id == 'emailaccounts') {
				$db->executeUpdate("
					UPDATE ticket_triggers
					SET run_order = ?
					WHERE email_account_id IS NOT NULL AND event_trigger = 'newticket'
				", array($x));
			} else {
				$tr_id = (int)$tr_id;
				$db->update('ticket_triggers', array('run_order' => $x), array('id' => $tr_id));
			}
		}

		$db->commit();
	}
}
