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
use Application\DeskPRO\Entity\TicketProcLog;
use Application\DeskPRO\Monolog\Logger as DpLogger;
use Application\DeskPRO\Tickets\Actions\ActionApplicator;
use Application\DeskPRO\Tickets\Actions\SendAgentAlert;
use Application\DeskPRO\Tickets\TicketSaveActions;
use DeskPRO\Kernel\KernelErrorHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Orb\Util\Strings;
use Orb\Util\Util;

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
	 * @var \Application\DeskPRO\Tickets\TicketSaveActions\TicketSaveActionInterface[]
	 */
	private $save_actions;

	/**
	 * @var \Application\DeskPRO\Tickets\TicketSaveActions\TicketSaveActionInterface[]
	 */
	private $post_save_actions;

	/**
	 * @var \Application\DeskPRO\BlobStorage\DeskproBlobStorage
	 */
	private $blob_storage;

	/**
	 * @var array
	 */
	private $auto_vars = array();


	/**
	 * @param DeskproContainer $container
	 */
	public function __construct(DeskproContainer $container)
	{
		$this->container = $container;
		$this->em = $container->getEm();
		$this->db = $container->getDb();
		$this->blob_storage = $container->getBlobStorage();

		$this->save_actions      = array();
		$this->post_save_actions = array();

		$this->save_actions[] = new TicketSaveActions\VerifyCreationSystem();
		$this->save_actions[] = new TicketSaveActions\VerifyDepartment($container->getTicketDepartments());
		$this->save_actions[] = new TicketSaveActions\VerifyRef($container->getRefGenerator());
		$this->save_actions[] = new TicketSaveActions\VerifyOrgManagers($container->getEm()->getRepository('DeskPRO:Organization'));

		$this->post_save_actions[] = new TicketSaveActions\ExecTriggers($container->getEm()->getRepository('DeskPRO:TicketTrigger'), new ActionApplicator($container));
		$this->post_save_actions[] = new TicketSaveActions\SetActionTimes();
		$this->post_save_actions[] = new TicketSaveActions\ApplySlas($container->getEm()->getRepository('DeskPRO:Sla')->getAutoSlas(), $container->getEm());
		$this->post_save_actions[] = new TicketSaveActions\RecalculateSlas($container->getEm(), new ActionApplicator($container));
		$this->post_save_actions[] = new TicketSaveActions\SaveTicketLogs($container->getEm());
		$this->post_save_actions[] = new TicketSaveActions\RunFilterUpdates($container->getEm(), $container->getTicketFilterChangeDetector());
		$this->post_save_actions[] = new TicketSaveActions\RecalculateTicketStats($container->getAgentData()->getIds(), $container->getDb());
	}


	/**
	 * Clears auto context vars
	 */
	public function clearAutoContextVars()
	{
		$this->auto_vars = array();
	}


	/**
	 * Adds an array of vars to auto context vars
	 *
	 * @param array $vars
	 */
	public function addAutoContextVars(array $vars)
	{
		$this->auto_vars = array_merge($this->auto_vars, $vars);
	}


	/**
	 * Gets array of currently set context vars
	 *
	 * @return array
	 */
	public function getAutoContextVars()
	{
		return $this->auto_vars;
	}


	/**
	 * Set an auto context var
	 *
	 * @param string $k
	 * @param mixed d$v
	 */
	public function setAutoContextVar($k, $v)
	{
		$this->auto_vars[$k] = $v;
	}


	/**
	 * Unset an auto context var
	 * @param string $k
	 */
	public function unsetAutoContextVar($k)
	{
		unset($this->auto_vars[$k]);
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
		$context->getLogger()->debug(sprintf("StateChanges: %s", implode(', ', $ticket->getStateChangeRecorder()->getChangedFields())));

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

		foreach ($this->save_actions as $action) {
			$context->getLogger()->info(sprintf("[TicketManager:saveaction] %s", Util::getBaseClassname($action)));
			$action->processTicket($ticket, $context);
		}

		if (!$is_noop && !$ticket->ticket_hash) {
			$ticket->recomputeHash();
		}

		$this->em->flush();

		foreach ($this->post_save_actions as $action) {
			$context->getLogger()->info(sprintf("[TicketManager:postsaveaction] %s", Util::getBaseClassname($action)));
			$action->processTicket($ticket, $context);
		}

		$this->em->flush();

		if (!$is_noop) {
			$logs = $context->getVars()->get('ticket_logs', array());

			$agent_alert_action = new SendAgentAlert(array(
				'agent_ids'   => array('notify_list'),
				'ticket_logs' => $logs
			));
			$agent_alert_action->setContainer($this->container);
			$agent_alert_action->applyAction($ticket, $context);
		}

		$this->db->insert('client_messages', array(
			'channel' => 'agent.ticket-updated',
			'auth' => Strings::random(15, Strings::CHARS_KEY),
			'date_created' => date('Y-m-d H:i:s'),
			'data' => serialize(array(
				'ticket_id'      => $ticket->getId(),
				'changed_fields' => $ticket->getStateChangeRecorder()->getChangedFields(),
				'via_person'     => $context->getPersonContext() ? $context->getPersonContext()->getId() : null
			))
		));

		$search_updater = new TicketSearchUpdater($this->db, $ticket);
		$search_updater->update();

		$this->em->flush();

		#----------------------------------------
		# Done
		#----------------------------------------

		$context->getLogger()->info(sprintf("########## END SAVE TICKET -- %s -- %.4fs ##########", $ticket->id ?: 0, microtime(true) - $time_start));

		$ticket->resetStateChangeRecorder();
		$ticket->__dp_last_process_save = $ticket->getStateChangeRecorder()->getStateVersion();

		if (!$is_noop && $ticket->getStatusCode() != 'hidden.deleted' && $context->getLogger() instanceof DpLogger) {
			$log_text = $context->getLogger()->getSavedMessages();
			if ($log_text) {
				try {
					$blob = $this->blob_storage->createBlobRecordFromString($log_text, 'ticket-manager.' . date('Y-m-d_H-i-s') . '.log', 'plain/text');
					$proc_log = new TicketProcLog();
					$proc_log->ticket = $ticket;
					$proc_log->blob = $blob;

					$this->em->persist($proc_log);
					$this->em->flush();
				} catch (\Exception $e) {
					KernelErrorHandler::logException($e);
				}
			}
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
		$context->getVars()->setArray($this->auto_vars);

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
		$context->getVars()->setArray($this->auto_vars);

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
		$context->getVars()->setArray($this->auto_vars);
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
		$logger->enableSavedMessages();

		if ($logfile = dp_get_config('debug.enable_ticket_log')) {
			if ($logfile === true || $logfile === 1 || $logfile === '1' || $logfile === "true") {
				$logfile = dp_get_log_dir() . '/ticket.log';
			}
			$stream = new StreamHandler($logfile);
			$logger->pushHandler($stream);
		}

		if (defined('DP_INTERFACE') && DP_INTERFACE == 'cli' && (in_array('--verbose', $_SERVER['argv']) || in_array('-v', $_SERVER['argv']))) {
			$stream = new StreamHandler('php://stdout');
			$logger->pushHandler($stream);
		}

		return $logger;
	}
}