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

use Doctrine\ORM\Mapping as ORM_Mapping;

use Application\DeskPRO\App;
use Orb\Util\Dates;

/**
 * Ticket triggers
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketTrigger")
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="ticket_triggers")
 */
class TicketTrigger extends \Application\DeskPRO\Domain\DomainObject
{
	const EVENT_NEW_TICKET                 = 'new_ticket';
	const EVENT_NEW_REPLY                  = 'new_reply';
	const EVENT_PROPERTY_CHANGE            = 'property_change';
	const EVENT_TIME_OPEN                  = 'time_open';
	const EVENT_TIME_USER_WAITING          = 'time_user_waiting';
	const EVENT_TIME_TOTAL_USER_WAITING    = 'time_total_user_waiting';
	const EVENT_TIME_AGENT_WAITING         = 'time_agent_waiting';
	const EVENT_TIME_RESOLVED              = 'time_resolved';

	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY")
	 * @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="event_trigger", type="string", length=50)
	 */
	protected $event_trigger;

	/**
	 * This is a number in seconds, or a number<space>scale.
	 *
	 * For example: 12 days
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="event_trigger_option", type="string", length=255)
	 */
	protected $event_trigger_option = '';

	/**
	 * @var bool
	 * @ORM_Mapping\Column(name="is_enabled", type="boolean")
	 */
	protected $is_enabled = true;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="terms", type="array")
	 */
	protected $terms = array();

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="actions", type="array")
	 */
	protected $actions = array();

	/**
	 * When non-null, the group is a special system trigger (hidden from most interfaces).
	 * Used prefixes: "urgency." for urgency-type triggers.
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="sys_name", type="string", length="50", nullable=true)
	 */
	protected $sys_name = null;

	/**
	 * If this trigger has any terms or actions with urgency, means they are
	 * listed on the urgency page.
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="has_urgency", type="boolean")
	 */
	protected $has_urgency = false;

	/**
	 * Go through the actions on this trigger and find $name, and then
	 * return its info.
	 *
	 * @param string $name
	 * @return array
	 */
	public function getActionInfoOfType($name)
	{
		foreach ($this->actions as $info) {
			if ($info['type'] == $name) {
				unset($info['type']);
				return $info['options'];
			}
		}

		return null;
	}

	/**
	 * Go through the terms on this trigger and find $name, and then
	 * return its info.
	 *
	 * @param string $name
	 * @return array
	 */
	public function getTermInfoOfType($name)
	{
		foreach ($this->terms as $info) {
			if ($info['type'] == $name) {
				unset($info['type']);
				return array_merge(array('op' => $info['op'], $info['options']));
			}
		}

		return null;
	}

	/**
	 * Check to see if a ticket matches
	 *
	 * @param Ticket $ticket
	 * @return bool
	 */
	public function isTriggerMatch(Ticket $ticket, \Application\DeskPRO\Tickets\TicketChangeTracker $tracker)
	{
		$this->terms = (array)$this->terms;

		$ticket_terms = new \Application\DeskPRO\Tickets\TicketTerms($this->terms);
		$ticket_terms->setChangeTracker($tracker);

		$match = $ticket_terms->doesTicketMatch($ticket);

		return $match;
	}



	/**
	 * Run any non-editor actions now. For example, callbacks.
	 */
	public function performExternalActions(Ticket $ticket, array $logs = array())
	{
		foreach ($this->actions as $action) {
			if ($action['type'] == 'trigger_plugin') {
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
				$qb->where("t.status IN('awaiting_agent','awaiting_user') AND t.date_created < :date_cut");
				break;
			case self::EVENT_TIME_USER_WAITING:
				$qb->where("t.status = 'awaiting_agent' AND t.date_user_waiting < :date_cut");
				break;
			case self::EVENT_TIME_AGENT_WAITING:
				$qb->where("t.status = 'awaiting_agent' AND t.date_user_waiting < :date_cut");
				break;
		}

		$tickets = $qb->exeute($params);

		return $tickets;
	}


	/**
	 * @ORM_Mapping\PostRemove
	 */
	public function _removeAssocPlugins()
	{
		App::getOrm()->beginTransaction();

		foreach ($this->actions as $action) {
			if ($action['type'] == 'trigger_plugin') {
				$plugin = App::getEntityRepository('DeskPRO:Plugin')->find($action['plugin_id']);
				if ($plugin['associated_object'] == "TicketTrigger:{$this->id}") {
					App::getOrm()->remove($plugin);
				}
			}
		}

		App::getOrm()->flush();
		App::getOrm()->commit();
	}



	/**
	 * @return string
	 */
	public function getTriggerType()
	{
		if (strpos($this->event_trigger, 'time_') === 0) {
			return 'escalation';
		} else {
			return 'trigger';
		}
	}


	public function getOptionTime()
	{
		if (!$this->event_trigger_option) {
			return 0;
		}

		if (strpos($this->event_trigger_option, ' ') === false) {
			return $this->event_trigger_option;
		}

		list ($time, ) = explode(' ', $this->event_trigger_option);
		return $time;
	}

	public function getOptionScale()
	{
		if (!$this->event_trigger_option) {
			return 0;
		}

		if (strpos($this->event_trigger_option, ' ') === false) {
			return 'secs';
		}

		list (, $scale) = explode(' ', $this->event_trigger_option);
		return $scale;
	}

	public function getOptionSeconds()
	{
		$time = $this->getOptionTime();
		$scale = $this->getOptionScale();

		$secs = 0;

		switch ($scale) {

			case 'hours':
				$secs = $time * Dates::SECS_HOUR;
				break;

			case 'days':
				$secs = $time * Dates::SECS_DAY;
				break;

			case 'weeks':
				$secs = $time * Dates::SECS_WEEK;
				break;

			case 'months':
				$secs = $time * Dates::SECS_MONTH;
				break;

			default:
				$secs = $time;
				break;
		}

		return $secs;
	}
}
