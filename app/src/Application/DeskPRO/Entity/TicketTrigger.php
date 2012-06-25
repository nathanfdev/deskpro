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

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

use Application\DeskPRO\App;
use Orb\Util\Dates;

/**
 * Ticket triggers
 *
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
	 */
	protected $id = null;

	/**
	 * @var string
	 */
	protected $title = '';

	/**
	 * @var string
	 */
	protected $event_trigger;

	/**
	 * This is a number in seconds, or a number<space>scale.
	 *
	 * For example: 12 days
	 *
	 * @var string
	 */
	protected $event_trigger_option = '';

	/**
	 * @var bool
	 */
	protected $is_enabled = true;

	/**
	 * @var string
	 */
	protected $terms = array();

	/**
	 * @var string
	 */
	protected $actions = array();

	/**
	 * When non-null, the group is a special system trigger (hidden from most interfaces).
	 * Used prefixes: "urgency." for urgency-type triggers.
	 *
	 * @var bool
	 */
	protected $sys_name = null;

	/**
	 * @var int
	 */
	protected $run_order = 0;

	/**
	 * @var \Application\DeskPRO\Tickets\TicketActions\ActionsCollection
	 */
	protected $_ticket_actions_coll;

	/**
	 * @var \Application\DeskPRO\Tickets\TicketTerms
	 */
	protected $_ticket_terms;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

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
	 * Get a searcher with the criteria terms. This is used in th
	 */
	public function getSearcher()
	{
		$searcher = new \Application\DeskPRO\Searcher\TicketSearch();

		foreach ($this->terms as $term) {
			$searcher->addTerm($term['type'], $term['op'], $term['options']);
		}

		$time_secs = $this->getOptionSeconds();

		switch ($this->event_trigger) {
			case self::EVENT_TIME_OPEN:
				$searcher->addTerm('status', 'is', array('awaiting_user', 'awaiting_agent'));
				$searcher->addRawWhere('tickets.date_user_waiting IS NOT NULL');

				$date_cut = new \DateTime('-' . $time_secs . ' seconds');
				$searcher->addTerm('date_created', 'lte', array('date1' => $date_cut));

				break;

			case self::EVENT_TIME_USER_WAITING:
				$searcher->addTerm('status', 'is', array('awaiting_agent'));
				$searcher->addRawWhere('tickets.date_user_waiting IS NOT NULL');

				$date_cut = new \DateTime('-' . $time_secs . ' seconds');
				$searcher->addTerm('user_waiting', 'lte', array('date1' => $date_cut));

				break;

			case self::EVENT_TIME_TOTAL_USER_WAITING:
				$searcher->addTerm('status', 'is', array('awaiting_agent'));
				$searcher->addRawWhere('tickets.date_user_waiting IS NOT NULL');
				$searcher->addTerm('total_user_waiting', 'between', array($time_secs, $time_secs));
				break;

			case self::EVENT_TIME_AGENT_WAITING:
				$searcher->addTerm('status', 'is', array('awaiting_user'));
				$searcher->addRawWhere('tickets.date_agent_waiting IS NOT NULL');

				$date_cut = new \DateTime('-' . $time_secs . ' seconds');
				$searcher->addTerm('agent_waiting', 'lte', array('date1' => $date_cut));

				break;

			case self::EVENT_TIME_RESOLVED:
				$searcher->addTerm('status', 'is', array('resolved'));
				$searcher->addRawWhere('tickets.date_resolved IS NOT NULL');

				$date_cut = new \DateTime('-' . $time_secs . ' seconds');
				$searcher->addTerm('date_resolved', 'lte', array('date1' => $date_cut));

				break;
		}

		return $searcher;
	}

	/**
	 * Gets the relevant time field on ticket for a particular ticket trigger.
	 * For example, 'EVENT_TIME_USER_WAITING' is dependant on ticket.date_user_waiting
	 *
	 * @return string
	 */
	public function getTicketTimeField()
	{
		switch ($this->event_trigger) {
			case self::EVENT_TIME_OPEN:
			case self::EVENT_TIME_USER_WAITING:
			case self::EVENT_TIME_TOTAL_USER_WAITING:
				return 'date_user_waiting';

			case self::EVENT_TIME_AGENT_WAITING:
				return 'date_agent_waiting';
				break;

			case self::EVENT_TIME_RESOLVED:
				return 'date_resolved';
		}

		return null;
	}


	/**
	 * @return \Application\DeskPRO\Tickets\TicketTerms
	 */
	public function getTicketTerms()
	{
		if ($this->_ticket_terms) return $this->_ticket_terms;
		$this->terms = (array)$this->terms;

		$ticket_terms = new \Application\DeskPRO\Tickets\TicketTerms($this->terms);

		$this->_ticket_terms = $ticket_terms;
		return $this->_ticket_terms;
	}

	/**
	 * Check to see if a ticket matches
	 *
	 * @param Ticket $ticket
	 * @return bool
	 */
	public function isTriggerMatch(Ticket $ticket, \Application\DeskPRO\Tickets\TicketChangeTracker $tracker)
	{
		$ticket_terms = $this->getTicketTerms();
		$ticket_terms->setChangeTracker($tracker);

		$match = $ticket_terms->doesTicketMatch($ticket);

		return $match;
	}


	/**
	 * @return array
	 */
	public function getTermDescriptions()
	{
		return $this->getTicketTerms()->getDescriptions();
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
	 * @return \Application\DeskPRO\Tickets\TicketActions\ActionsCollection
	 */
	public function getTicketActionsCollection()
	{
		if ($this->_ticket_actions_coll) return $this->_ticket_actions_coll;

		$factory = new \Application\DeskPRO\Tickets\TicketActions\ActionsFactory();
		$actions_collection = new \Application\DeskPRO\Tickets\TicketActions\ActionsCollection();

		foreach ($this->actions as $action_info) {
			$action = $factory->createFromInfo($action_info);
			if ($action) {
				$actions_collection->add($action);
			}
		}

		$this->_ticket_actions_coll = $actions_collection;
		return $this->_ticket_actions_coll;
	}


	/**
	 * @return array
	 */
	public function getActionDescriptions()
	{
		return $this->getTicketActionsCollection()->getDescriptions();
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
			case self::EVENT_TIME_OPEN:
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
		if (!$this->event_trigger_option || strpos($this->event_trigger_option, ' ') === false) {
			return 'seconds';
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

			case 'minutes':
				$secs = $time * Dates::SECS_MIN;
				break;

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

	public function setEventTriggerOption($opt)
	{
		$this->setModelField('event_trigger_option', $opt);

		// For time fields, the run order is based off their time.
		// Admins never see this num so its perfect to keep ordering queryies
		// the same everywhere.
		$this->setModelField('run_order', $this->getOptionSeconds());
	}

	/**
	 * Gets the logical trigger group based on the event type and the criteria.
	 * For example, there is one "new ticket" type but depending on who and how the ticket created,
	 * it might be new_ticket.user_web, new_ticket.user_email or new_ticket.agent.
	 */
	public function getTriggerGroup()
	{
		switch ($this->event_trigger) {
			case self::EVENT_NEW_TICKET:
				$type = $this->getTicketTerms()->getTicketTerm('creation_system');
				$type = isset($type['options']['creation_system']) ? $type['options']['creation_system'] : 'web.person';

				switch ($type) {
					case 'web.person':
						return 'new_ticket.web_person';
					case 'gateway.person':
						return 'new_ticket.gateway_person';
					case 'widget':
						return 'new_ticket.widget';
					case 'gateway.agent':
					case 'web.agent':
						return 'new_ticket.agent';
					default:
						return 'new_ticket';
				}
				break;

			case self::EVENT_NEW_REPLY:
				$type = $this->getTicketTerms()->getTicketTerm('creation_system');
				$type = isset($type['options']['creation_system']) ? $type['options']['creation_system'] : 'web.person';

				$who_type = $this->getTicketTerms()->getTicketTerm('action_performer');
				$who_type = isset($who_type['options']['action_performer']) ? $who_type['options']['action_performer'] : 'user';

				if ($who_type == 'agent') {
					return 'new_reply.agent';
				}

				if ($type == 'web') {
					if ($who_type == 'user') {
						return 'new_reply.web_person';
					}
				} elseif ($type == 'gateway') {
					if ($who_type == 'user') {
						return 'new_reply.gateway_person';
					}
				}

				return 'new_reply';

				break;

			case self::EVENT_PROPERTY_CHANGE:

				$who_type = $this->getTicketTerms()->getTicketTerm('action_performer');
				$who_type = isset($who_type['options']['action_performer']) ? $who_type['options']['action_performer'] : 'user';

				if ($who_type == 'user') {
					return 'property_change.user';
				} else {
					return 'property_change.agent';
				}

				return 'property_change';

				break;

			default:
				return $this->event_trigger;
		}

		return 'other';
	}


	/**
	 * Get an array of special term types for the trigger.
	 * For example, a 'new_ticket.web_gateway' always has the creation_system term. It's not changable.
	 *
	 * @return array
	 */
	public function getStaticTermTypes()
	{
		switch ($this->event_trigger) {
			case 'new_ticket': return array('creation_system');
			case 'new_reply': return array('creation_system', 'action_performer');
			case 'property_change': return array('action_performer');
		}

		return array();
	}


	/**
	 * Get the actual set terms of the static types
	 *
	 * @return array
	 */
	public function getStaticTerms()
	{
		$types = $this->getStaticTermTypes();
		if (!$types) {
			return array();
		}

		$ret = array();

		foreach ($this->terms as $term_info) {
			if (in_array($term_info['type'], $types)) {
				$ret[] = $term_info;
			}
		}

		return $ret;
	}


	/**
	 * @param array $terms
	 */
	public function setTerms(array $terms)
	{
		$this->setModelField('terms', $terms);
		$this->_ticket_terms = null;
	}


	public function isUneditable()
	{
		if (!$this->sys_name) {
			return false;
		}

		static $uneditable = array(
			'email_validation.web' => 1,
			'email_validation.email' => 1,
			'email_validation.widget' => 1,
		);

		return isset($uneditable[$this->sys_name]);
	}

	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketTrigger';
		$metadata->setPrimaryTable(array( 'name' => 'ticket_triggers', ));
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->addLifecycleCallback('_removeAssocPlugins', 'postRemove');
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true, ));
		$metadata->mapField(array( 'fieldName' => 'title', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'title', ));
		$metadata->mapField(array( 'fieldName' => 'event_trigger', 'type' => 'string', 'length' => 50, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'event_trigger', ));
		$metadata->mapField(array( 'fieldName' => 'event_trigger_option', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'event_trigger_option', ));
		$metadata->mapField(array( 'fieldName' => 'is_enabled', 'type' => 'boolean', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'is_enabled', ));
		$metadata->mapField(array( 'fieldName' => 'terms', 'type' => 'array', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'terms', ));
		$metadata->mapField(array( 'fieldName' => 'actions', 'type' => 'array', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'actions', ));
		$metadata->mapField(array( 'fieldName' => 'sys_name', 'type' => 'string', 'length' => 50, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'columnName' => 'sys_name', ));
		$metadata->mapField(array( 'fieldName' => 'run_order', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'run_order', ));
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
	}
}
