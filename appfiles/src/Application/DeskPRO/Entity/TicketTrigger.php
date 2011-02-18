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

namespace Application\DeskPRO\Entity;

use \Application\DeskPRO\App;

/**
 * Ticket queues
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketTrigger")
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="ticket_triggers")
 */
class TicketTrigger extends \Application\DeskPRO\Domain\DomainObject
{
	const EVENT_NEW_TICKET           = 'new_ticket';
	const EVENT_NEW_REPLY            = 'new_reply';
	const EVENT_PROPERTY_CHANGE      = 'property_change';
	const EVENT_TIME_UNRESOLVED      = 'time_unresolved';
	const EVENT_TIME_USER_WAITING    = 'time_user_waiting';
	const EVENT_TIME_AGENT_WAITING   = 'time_agent_waiting';

	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY")
	 * @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var string
	 * @orm:Column(name="event_trigger", type="string", length=50)
	 */
	protected $event_trigger;

	/**
	 * @var string
	 * @orm:Column(name="event_trigger_option", type="string", length=255)
	 */
	protected $event_trigger_option = '';

	/**
	 * @var bool
	 * @orm:Column(name="is_enabled", type="boolean")
	 */
	protected $is_enabled = true;

	/**
	 * @var string
	 * @orm:Column(name="terms", type="array")
	 */
	protected $terms = array();

	/**
	 * @var string
	 * @orm:Column(name="actions", type="array")
	 */
	protected $actions = array();



	/**
	 * Check to see if a ticket matches
	 *
	 * @param Entity\Ticket $ticket
	 * @return bool
	 */
	public function checkTicketMatch(Ticket $ticket, array $logs = array())
	{
		$this->terms = (array)$this->terms;

		$ticket_terms = new \Application\DeskPRO\Tickets\TicketTerms($this->terms);
		$match = $ticket_terms->doesTicketMatch($ticket);

		// Check some specific terms only we know about here
		if ($match) {
			foreach ($this->terms as $term => $info) {
				list($op, $choice) = $info;

				switch ($term) {
					case 'message_type':
						if (!isset($logs['message_created'])) {
							return false;
						}
						$message = $logs['message_created']->getMessage();

						$is = false;
						if ($message['is_agent_note'] AND $info['type'] == 'note') {
							$is = true;
						} elseif ($message['person']['is_agent'] AND $info['type'] == 'agent_reply') {
							$is = true;
						} elseif ($info['type'] == 'user_reply') {
							$is = true;
						}

						if (!($op == 'is' AND $is) AND !($op == 'not' AND !$is)) {
							return false;
						}
						break;

				}
			}
		}

		return $match;
	}



	/**
	 * Gets actions we can perform against the ticket with a ticket editor object.
	 *
	 * @param Entity\Ticket $ticket
	 * @return array
	 */
	public function getEditActions(Ticket $ticket, array $logs = array())
	{
		$ticket_actions = new \Application\DeskPRO\Tickets\TicketActions($this->actions);
		$actions = $ticket_actions->getActionsArray($ticket);

		return $actions;
	}



	/**
	 * Run any non-editor actions now. For example, callbacks.
	 */
	public function performExternalActions(Ticket $ticket, array $logs = array())
	{
		foreach ($this->actions as $action) {
			if ($action['rule_type'] == 'trigger_plugin') {
				$plugin = App::getEntityRepository('DeskPRO:Plugin')->find($action['plugin_id']);
				$plugin->executePlugin(array(
					'ticket' => $ticket,
					'logs' => $logs
				));
			}
		}
	}



	/**
	 * Get an array of tickets that should be escalated now based on the current
	 * time trigger.
	 *
	 * @return array
	 */
	public function findEscaltedTickets()
	{
		if (strpos($this->event_trigger, 'time_') !== 0) {
			throw new \BadMethodCallException('This method is only valid for time-based triggers');
		}

		$date = new \DateTime("-{$this->event_trigger_option} seconds");

		$qb = App::getOrm()->createQueryBuilder()
			->select('t')
			->from('DeskPRO:Ticket', 't');

		$params = array('date_cut' => $date);
		switch ($this->event_trigger) {
			case self::EVENT_TIME_UNRESOLVED:
				$qb->where("t.status IN('open','pending') AND t.date_created < :date_cut");
				break;
			case self::EVENT_TIME_USER_WAITING:
				$qb->where("t.status = 'open' AND t.date_user_waiting < :date_cut");
				break;
			case self::EVENT_TIME_AGENT_WAITING:
				$qb->where("t.status = 'open' AND t.date_user_waiting < :date_cut");
				break;
		}

		$tickets = $qb->exeute($params);

		return $tickets;
	}


	/**
	 * @orm:PostDelete
	 */
	public function _removeAssocPlugins()
	{
		App::getOrm()->beginTransaction();

		foreach ($this->actions as $action) {
			if ($action['rule_type'] == 'trigger_plugin') {
				$plugin = App::getEntityRepository('DeskPRO:Plugin')->find($action['plugin_id']);
				if ($plugin['associated_object'] == "TicketTrigger:{$this->id}") {
					App::getOrm()->remove($plugin);
				}
			}
		}

		App::getOrm()->flush();
		App::getOrm()->commit();
	}
}