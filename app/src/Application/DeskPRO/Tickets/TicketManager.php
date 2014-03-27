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

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Monolog\Logger as DpLogger;
use Application\DeskPRO\ORM\StateChange\ChangeTriggerLog;
use Application\DeskPRO\Tickets\Actions\ActionApplicatorInterface;
use Application\DeskPRO\Tickets\Actions\SendAgentAlert;
use Application\DeskPRO\Tickets\TicketLog\TicketLogGenerator;
use DeskPRO\Kernel\KernelErrorHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Orb\Util\Strings;

class TicketManager
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	private $db;

	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	private $container;

	/**
	 * @var ActionApplicatorInterface
	 */
	private $action_applicator;

	/**
	 * @param DeskproContainer $container
	 * @param ActionApplicatorInterface $action_applicator
	 */
	public function __construct(DeskproContainer $container, ActionApplicatorInterface $action_applicator)
	{
		$this->container = $container;
		$this->em = $container->getEm();
		$this->db = $container->getDb();

		$this->action_applicator = $action_applicator;
	}


	/**
	 * Create a new ticket object. When you are ready to persist it, call saveTicket().
	 *
	 * @return Ticket
	 */
	public function createTicket()
	{
		$ticket = new Ticket();
		$ticket->disableAutoTicketProcess();

		return $ticket;
	}


	/**
	 * Finds a ticket and returns it.
	 *
	 * NOTE: This will disable auto-ticket processing,
	 * which means if you make changes, you need to use the saveTicket() method to
	 * have those changes run the other related systems (like triggers etc).
	 *
	 * @param int $id
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	public function getTicket($id)
	{
		$ticket = $this->em->find('DeskPRO:Ticket', $id);
		if ($ticket) {
			$ticket->disableAutoTicketProcess();
		}
		return $ticket;
	}


	/**
	 * Disables auto-ticket processing on the ticket. This means you should save the ticket
	 * via $this->saveTicket().
	 *
	 * This is used when interacting with legacy code where ticket processing is expected to happen
	 * automatically.
	 *
	 * @param Ticket $ticket
	 */
	public function markAsManaged(Ticket $ticket)
	{
		$ticket->disableAutoTicketProcess();
	}


	/**
	 * Re-enables auto-ticket processing on the ticket.
	 *
	 * This is used when interacting with legacy code where ticket processing is expected to happen
	 * automatically.
	 *
	 * @param Ticket $ticket
	 */
	public function markAsUnmanaged(Ticket $ticket)
	{
		$ticket->enableAutoTicketProcess();
	}


	/**
	 * @param Ticket $ticket
	 * @param ExecutorContextInterface $context
	 * @throws \Exception
	 */
	public function saveTicket(Ticket $ticket, ExecutorContextInterface $context)
	{
		$this->db->beginTransaction();
		try {
			$ret = $this->doSaveTicket($ticket, $context);
			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $ret;
	}

	private function doSaveTicket(Ticket $ticket, ExecutorContextInterface $context)
	{
		// Noop is sometimes used when we need to save a ticket and have appropriate client-messages
		// sent to update agent filters, but we dont want the usual triggers etc to run.
		// This is usually done when the ticket is being deleted.
		$is_noop = $context->getEventType() == 'noop';

		$time_start = microtime(true);
		$context->getLogger()->info(sprintf("########## START SAVE TICKET -- %s ##########", $ticket->id ? $ticket->id : 'newticket'));

		$context->getLogger()->debug(sprintf("EventType: %s", $context->getEventType()));
		$context->getLogger()->debug(sprintf("EventMethod: %s", $context->getEventMethod()));
		$context->getLogger()->debug(sprintf("EventPerformer: %s", $context->getEventPerformer()));

		if ($context->getPersonContext()) {
			$context->getLogger()->debug(sprintf(
				"PersonContext: <Person:%d> %s %s",
				$context->getPersonContext()->id,
				$context->getPersonContext()->getDisplayName(),
				$context->getPersonContext()->getPrimaryEmailAddress()
			));
		} else {
			$context->getLogger()->debug("PersonContext: NULL");
		}

		$this->em->persist($ticket);

		#----------------------------------------
		# Set the creation system
		#----------------------------------------

		if (!$is_noop) {
			$this->_doSaveTicket_verifyCreationSystem($ticket, $context);
			$this->_doSaveTicket_verifyDepartment($ticket, $context);
			$this->_doSaveTicket_verifyRef($ticket, $context);
			$this->_doSaveTicket_verifyOrgManagers($ticket, $context);
			$this->_doSaveTicket_execTriggers($ticket, $context);
			$this->_doSaveTicket_recalcSlas($ticket, $context);

			if (!$ticket->ticket_hash) {
				$ticket->recomputeHash();
			}
		}

		$this->em->flush();

		if (!$is_noop) {
			$logs = $this->_doSaveTicket_runTicketLog($ticket, $context);
		} else {
			$logs = array();
		}

		$this->_doSaveTicket_runFilterUpdates($ticket, $context);

		if (!$is_noop) {
			$this->_doSaveTicket_recalcStats($ticket, $context);

			$agent_alert_action = new SendAgentAlert(array(
				'agent_ids'   => array('notify_list'),
				'ticket_logs' => $logs
			));
			$agent_alert_action->setContainer($this->container);
			$agent_alert_action->applyAction($ticket, $context);

			$search_updater = new TicketSearchUpdater($this->db, $ticket);
			$search_updater->update();
		}

		#----------------------------------------
		# Done
		#----------------------------------------

		$this->em->flush();
		$context->getLogger()->info(sprintf("########## END SAVE TICKET -- %s -- %.4fs ##########", $ticket->id ?: 0, microtime(true) - $time_start));

		$ticket->resetStateChangeRecorder();
		$ticket->__dp_last_process_save = $ticket->getStateChangeRecorder()->getStateVersion();
	}

	private function _doSaveTicket_verifyCreationSystem(Ticket $ticket, ExecutorContextInterface $context)
	{
		if (!$ticket->creation_system) {
			if ($context->getEventMethod() == 'email') {
				$creation_system = 'gateway.';

				if ($context->getEventPerformer() == 'agent') {
					$creation_system .= 'agent';
				} else {
					$creation_system .= 'person';
				}
			} else if ($context->getEventMethod() == 'api') {
				$creation_system = 'web.api.';

				if ($context->getEventPerformer() == 'agent') {
					$creation_system .= 'agent';
				} else {
					$creation_system .= 'person';
				}
			} else {
				$creation_system = 'web.';

				if ($context->getEventPerformer() == 'agent') {
					$creation_system .= 'agent.portal';
				} else {
					if ($context->getEventMethodOption('is_widget')) {
						$creation_system .= 'person.widget';
					} else if ($context->getEventMethodOption('is_embedded')) {
						$creation_system .= 'person.embed';
					} else {
						$creation_system .= 'person.portal';
					}
				}
			}

			$ticket->creation_system = $creation_system;

			if ($context->getEventMethodOption('origin_url')) {
				$ticket->creation_system_option = $context->getEventMethodOption('origin_url');
			}
		}
	}

	private function _doSaveTicket_verifyDepartment(Ticket $ticket, ExecutorContextInterface $context)
	{
		/** @var \Application\DeskPRO\Departments\TicketDepartments $ticket_deps */
		$ticket_deps = $this->container->getSystemService('TicketDepartments');

		if (!$ticket->department) {
			$ticket->department = $ticket_deps->getDefaultDepartment();
		}

		if ($ticket_deps->getChildren($ticket->department)) {
			$ticket->department = $ticket_deps->getDefaultDepartment();
		}
	}

	private function _doSaveTicket_verifyRef(Ticket $ticket, ExecutorContextInterface $context)
	{
		if (!$ticket->ref) {
			try {
				$ticket->ref = $this->container->getRefGenerator()->generateReference('DeskPRO:Ticket');
			} catch (\Exception $e) {
				KernelErrorHandler::logException($e);

				// Using a custom format.
				// We just ran into a collision which means the pattern is not a good pattern.
				// We are going to append a random number automatically if it isn't part of the pattern already
				if ($this->container->getSetting('core.ref_pattern') && strpos($this->container->getSetting('core.ref_pattern'), '<?>') === -1 && strpos($this->container->getSetting('core.ref_pattern'), '<A>') === -1) {
					$set_pattern = $this->container->getSetting('core.ref_pattern');
					$set_pattern .= '-<A><A><A>';
					$this->container->getSettingsHandler()->setSetting('core.ref_pattern', $set_pattern);
				}

				// Log and fallback to a random ref
				$ref = Strings::random(4, Strings::CHARS_ALPHA_IU) . '-' . Strings::random(4, Strings::CHARS_NUM) . '-' . Strings::random(4, Strings::CHARS_ALPHA_IU) . '-' . date('ymd');
				$ticket->ref = $ref;
			}
		}
	}

	private function _doSaveTicket_verifyOrgManagers(Ticket $ticket, ExecutorContextInterface $context)
	{
		if ($ticket->organization) {
			$managers = $this->em->getRepository('DeskPRO:Organization')->getManagers($this->organization);
			foreach ($managers AS $manager) {
				if ($manager->getPref('org.manager_auto_add')) {
					$ticket->addParticipantPerson($manager);
				}
			}
		}
	}

	private function _doSaveTicket_execTriggers(Ticket $ticket, ExecutorContextInterface $context)
	{
		$state = $ticket->getStateChangeRecorder();

		$triggers = $this->em->createQuery("
			SELECT t
			FROM DeskPRO:TicketTrigger t
			WHERE t.event_trigger = :event_type
			ORDER BY t.run_order
		")->execute(array('event_type' => $context->getEventType()));

		/** @var \Application\DeskPRO\Entity\TicketTrigger[] $triggers */
		foreach ($triggers as $trigger) {
			if ($context->getVars()->has('stop_triggers')) {
				$context->getLogger()->info("[Triggers] Got stop signal");
				break;
			}

			$mode_var = null;
			switch ($context->getEventPerformer()) {
				case 'agent':
					$mode_var = $trigger->by_agent_mode;
					break;
				case 'user':
					$mode_var = $trigger->by_user_mode;
					break;
			}
			if ($mode_var) {
				$is_method_match = in_array($context->getEventMethod(), $mode_var);
			} else {
				$is_method_match = false;
			}
			if (!$is_method_match) {
				$context->getLogger()->info(sprintf("[Triggers] Skip trigger #%s due to method mismatch: %s != (%s) %s", $trigger->id, $context->getEventMethod(), $context->getEventPerformer() ?: '', implode(', ', $mode_var ?: array('NONE'))));
				continue;
			}

			$context->getLogger()->info(sprintf("[Triggers] ----- BEGIN TRIGGER #%s :: %s -----", $trigger->id, $trigger->title));
			$ts = microtime(true);

			$state->setCurrentChangeMetadata(array('trigger' => $trigger));

			$change = new ChangeTriggerLog(
				'trigger',
				$trigger->id,
				$trigger->title
			);
			$state->recordChange($change);

			$match = $trigger->terms->isTriggerMatch($ticket, $context);
			$context->getLogger()->info(sprintf("[Triggers] (#%d): %s", $trigger->id, $match ? "MATCH" : "no match"));

			if ($match) {
				try {
					$this->action_applicator->apply($trigger->actions, $ticket, $context);
				} catch (\Exception $e) {
					$context->getLogger()->error(sprintf("[Triggers] Exception: [%s] %s", $e->getCode(), $e->getMessage()), array('exception' => $e));
				}
			}

			$context->getLogger()->info(sprintf("[Triggers] ----- FINISH TRIGGER #%s :: %.4fs -----", $trigger->id, microtime(true)-$ts));

			$state->clearCurrentChangeMetaData();
		}
	}

	private function _doSaveTicket_recalcSlas(Ticket $ticket, ExecutorContextInterface $context)
	{
		$state = $ticket->getStateChangeRecorder();

		if ($state->isNewTicket() && !$ticket->hidden_status) {
			$reset_slas = false;
			$recalculate_slas = false;

			if ($state->hasChangedField('status') || $state->hasChangedField('hidden_status')) {
				$reset_slas = true;
				$recalculate_slas = true;
			}

			if ($state->hasChangedField('messages')) {
				$recalculate_slas = true;
			}

			if ($reset_slas || $recalculate_slas) {
				foreach ($ticket->ticket_slas AS $ticket_sla) {
					if ($reset_slas && !$ticket_sla->is_completed_set) {
						$ticket_sla->is_completed = false;
					}
					if ($recalculate_slas) {
						$ticket_sla->calculateSlaDates();
					}
					$this->em->persist($ticket_sla);
				}
			}
		}
	}

	private function _doSaveTicket_runTicketLog(Ticket $ticket, ExecutorContextInterface $context)
	{
		$ticketlog_generator = new TicketLogGenerator($ticket, $context);
		$logs = $ticketlog_generator->getLogEntries();

		foreach ($logs as $l) {
			$this->em->persist($l);
		}

		return $logs;
	}

	private function _doSaveTicket_runFilterUpdates(Ticket $ticket, ExecutorContextInterface $context)
	{
		$change_set = $this->container->getTicketFilterChangeDetector()->getFilterChangeSet($ticket, $context);
		$client_messages = $change_set->getListUpdateClientMessages();

		foreach ($client_messages as $cm) {
			$this->em->persist($cm);
		}
	}

	private function _doSaveTicket_recalcStats(Ticket $ticket, ExecutorContextInterface $context)
	{
		$state = $ticket->getStateChangeRecorder();

		if ($state->isNewTicket() || $state->hasChangedField('messages')) {
			$agent_ids_in = implode(',', $this->container->getAgentData()->getIds());

			$ticket->count_agent_replies = $this->db->fetchColumn("
				SELECT COUNT(*)
				FROM tickets_messages
				WHERE ticket_id = ? AND is_agent_note = 0 AND person_id IN ($agent_ids_in)
			", array($ticket->id));

			$ticket->count_user_replies = $this->db->fetchColumn("
				SELECT COUNT(*)
				FROM tickets_messages
				WHERE ticket_id = ? AND person_id NOT IN ($agent_ids_in)
			", array($ticket->id));
		}
	}

	/**
	 * @param Person $agent
	 * @param $event_type
	 * @param $event_method
	 * @param array $event_method_options
	 * @return ExecutorContextInterface
	 */
	public function createAgentExecutorContext(Person $agent = null, $event_type, $event_method, array $event_method_options = array())
	{
		$context = new ExecutorContext($this->createNewLogger());

		if ($agent) {
			$context->setPersonContext($agent);
		}

		$context->setEventPerformer('agent');
		$context->setEventType($event_type);
		$context->setEventMethod($event_method, $event_method_options);
		return $context;
	}


	/**
	 * @param Person $user
	 * @param $event_type
	 * @param $event_method
	 * @param array $event_method_options
	 * @return ExecutorContextInterface
	 */
	public function createUserExecutorContext(Person $user = null, $event_type, $event_method, array $event_method_options = array())
	{
		$context = new ExecutorContext($this->createNewLogger());

		if ($user) {
			$context->setPersonContext($user);
		}

		$context->setEventPerformer('user');
		$context->setEventType($event_type);
		$context->setEventMethod($event_method, $event_method_options);
		return $context;
	}


	/**
	 * @param string $event_type
	 * @param string $event_method
	 * @param array $event_method_options
	 * @return ExecutorContextInterface
	 */
	public function createSystemExecutorContext($event_type = 'system', $event_method = 'system', array $event_method_options = array())
	{
		$context = new ExecutorContext($this->createNewLogger());
		$context->setEventType($event_type);
		$context->setEventMethod($event_method, $event_method_options);
		return $context;
	}

	/**
	 * @return Logger
	 */
	protected function createNewLogger()
	{
		$logger = new DpLogger('tickets');

		$any = false;
		if ($logfile = dp_get_config('debug.enable_ticket_log')) {
			if ($logfile === true || $logfile === 1 || $logfile === '1' || $logfile === "true") {
				$logfile = dp_get_log_dir() . '/ticket.log';
			}
			$stream = new StreamHandler($logfile);
			$logger->pushHandler($stream);
			$any = true;
		}

		if (defined('DP_INTERFACE') && DP_INTERFACE == 'cli' && (in_array('--verbose', $_SERVER['argv']) || in_array('-v', $_SERVER['argv']))) {
			$stream = new StreamHandler('php://stdout');
			$logger->pushHandler($stream);
			$any = true;
		}

		if (!$any) {
			$logger->pushHandler(new NullHandler());
		}

		return $logger;
	}
}