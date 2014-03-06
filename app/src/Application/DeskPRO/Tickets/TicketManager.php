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
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Tickets\TicketLog\TicketLogGenerator;
use DeskPRO\Kernel\KernelErrorHandler;
use Monolog\Formatter\LineFormatter;
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
	 * @param DeskproContainer $container
	 */
	public function __construct(DeskproContainer $container)
	{
		$this->container = $container;
		$this->em = $container->getEm();
		$this->db = $container->getDb();
	}


	/**
	 * @param int $id
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	public function getTicket($id)
	{
		$ticket = $this->em->find('DeskPRO:Ticket', $id);
		return $ticket;
	}


	/**
	 * @param Ticket $ticket
	 * @param ExecutorContext $context
	 * @throws \Exception
	 */
	public function saveTicket(Ticket $ticket, ExecutorContext $context)
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

	private function doSaveTicket(Ticket $ticket, ExecutorContext $context)
	{
		$time_start = microtime(true);
		$context->getLogger()->info(sprintf("########## START SAVE TICKET -- %s ##########", $ticket->id ? $ticket->id : 'newticket'));

		$state = $ticket->getStateChangeRecorder();

		$this->em->persist($ticket);

		#----------------------------------------
		# Set the creation system
		#----------------------------------------

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

		#----------------------------------------
		# Set or verify the department
		#----------------------------------------

		/** @var \Application\DeskPRO\Departments\TicketDepartments $ticket_deps */
		$ticket_deps = $this->container->getSystemService('TicketDepartments');

		if (!$ticket->department) {
			$ticket->department = $ticket_deps->getDefaultDepartment();
		}

		if ($ticket_deps->getChildren($ticket->department)) {
			$ticket->department = $ticket_deps->getDefaultDepartment();
		}

		#----------------------------------------
		# Sort out ref
		#----------------------------------------

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

		#----------------------------------------
		# Automtically add org managers of the ticket
		#----------------------------------------

		if ($ticket->organization) {
			$managers = $this->em->getRepository('DeskPRO:Organization')->getManagers($this->organization);
			foreach ($managers AS $manager) {
				if ($manager->getPref('org.manager_auto_add')) {
					$ticket->addParticipantPerson($manager);
				}
			}
		}

		#----------------------------------------
		# Triggers
		#----------------------------------------

		$triggers = $this->em->createQuery("
			SELECT t
			FROM DeskPRO:TicketTrigger t
			WHERE t.event_trigger = :event_type
			ORDER BY t.run_order
		")->execute(array('event_type' => $context->getEventType()));

		/** @var \Application\DeskPRO\Entity\TicketTrigger[] $triggers */
		foreach ($triggers as $trigger) {
			if ($context->getVars()->has('stop_triggers')) {
				$context->getLogger()->info("Stopping triggers");
				break;
			}

			$context->getLogger()->info(sprintf("[Triggers] (#%d) ----- BEGIN %s -----", $trigger->id, $trigger->id));
			$ts = microtime(true);

			$match = $trigger->terms->isTriggerMatch($ticket, $context);
			$context->getLogger()->info(sprintf("[Triggers] (#%d): %s", $trigger->id, $match ? "MATCH" : "no match"));

			if ($match) {
				$trigger->actions->applyAction($ticket, $context);
			}

			$context->getLogger()->info(sprintf("[Triggers] (#%d) ----- FINISH %.4fs -----", $trigger->id, microtime(true)-$ts));
		}

		#----------------------------------------
		# Recalculate SLAs
		#----------------------------------------

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

		#----------------------------------------
		# Calculate ticket hash
		#----------------------------------------

		if (!$ticket->ticket_hash) {
			$ticket->recomputeHash();
		}

		#----------------------------------------
		# Initial flush
		#----------------------------------------

		$this->em->flush();

		#----------------------------------------
		# Ticket Log
		#----------------------------------------

		$ticketlog_generator = new TicketLogGenerator($ticket, $context);
		$logs = $ticketlog_generator->getLogEntries();

		foreach ($logs as $l) {
			$this->em->persist($l);
		}

		#----------------------------------------
		# Ticket Filter update
		#----------------------------------------

		$filter_change_detect = $context->createFilterChangeDetector($ticket);
		$client_messages = $filter_change_detect->getListUpdateClientMessages();

		foreach ($client_messages as $cm) {
			$this->em->persist($cm);
		}

		#----------------------------------------
		# Update search
		#----------------------------------------

		$search_updater = new TicketSearchUpdater($this->db, $ticket);
		$search_updater->update();

		#----------------------------------------
		# Recount stats
		#----------------------------------------

		if ($state->isNewTicket()) {
			$ticket->count_agent_replies = count($state->getNewAgentReplies());
			$ticket->count_user_replies  = count($state->getNewUserReplies());
		} else if ($state->hasChangedField('messages')) {
			$agent_ids_in = implode(',', $this->container->getAgentData()->getIds());

			$ticket->count_agent_replies = $this->db->fetchColumn("
				SELECT COUNT(*)
				FROM tickets_messages
				WHERE
					ticket_id = ?
					AND is_agent_note = 0
					AND person_id IN ($agent_ids_in)
			", array($ticket->id));

			$this->count_user_replies = $this->db->fetchColumn("
				SELECT COUNT(*)
				FROM tickets_messages
				WHERE
					ticket_id = ?
					AND person_id NOT IN ($agent_ids_in)
			", array($ticket->id));
		}

		#----------------------------------------
		# Done
		#----------------------------------------

		$this->em->flush();
		$context->getLogger()->info(sprintf("########## END SAVE TICKET -- %s -- %.4fs ##########", $ticket->id ?: 0, microtime(true) - $time_start));

		$ticket->resetStateChangeRecorder();
	}


	/**
	 * @param Person $agent
	 * @param $event_type
	 * @param $event_method
	 * @param array $event_method_options
	 * @return ExecutorContext
	 */
	public function createAgentExecutorContext(Person $agent, $event_type, $event_method, array $event_method_options = array())
	{
		$context = new ExecutorContext($this->container, $this->createNewLogger());
		$context->setPersonContext($agent);
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
	 * @return ExecutorContext
	 */
	public function createUserExecutorContext(Person $user, $event_type, $event_method, array $event_method_options = array())
	{
		$context = new ExecutorContext($this->container, $this->createNewLogger());
		$context->setPersonContext($user);
		$context->setEventPerformer('user');
		$context->setEventType($event_type);
		$context->setEventMethod($event_method, $event_method_options);
		return $context;
	}


	/**
	 * @param string $event_type
	 * @param string $event_method
	 * @param array $event_method_options
	 * @return ExecutorContext
	 */
	public function createSystemExecutorContext($event_type = 'system', $event_method = 'system', array $event_method_options = array())
	{
		$context = new ExecutorContext($this->container, $this->createNewLogger());
		$context->setEventType($event_type);
		$context->setEventMethod($event_method, $event_method_options);
		return $context;
	}

	/**
	 * @return Logger
	 */
	protected function createNewLogger()
	{
		$logger = new Logger('tickets');

		$formatter = new LineFormatter("[%datetime%] %message%\n");
		$stream_handler = new StreamHandler('php://stdout', 'DEBUG');
		$stream_handler->setFormatter($formatter);

		$logger->pushHandler($stream_handler);
		return $logger;
	}
}