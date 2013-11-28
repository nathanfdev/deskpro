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

use Application\DeskPRO\ORM\EntityManager;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class TicketManager
{
	/**
	 * @var
	 */
	private $em;

	/**
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
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
	 */
	public function saveTicket(Ticket $ticket, ExecutorContext $context)
	{
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
		# Ticket Log
		#----------------------------------------

		$state = $ticket->getStateChangeRecorder();

		foreach ($state->getChanges() as $change) {
			$context->getLogger()->info(sprintf("[ChageLog] %s", $change->getField()));
		}

		#----------------------------------------
		# Ticket Filter update
		#----------------------------------------

		$clone = $ticket->getOriginalStateClone();
	}


	/**
	 * @param Person $agent
	 * @param string $event_type
	 * @param string $event_method
	 * @return \Application\DeskPRO\Tickets\ExecutorContext
	 */
	public function createAgentExecutorContext(Person $agent, $event_type, $event_method)
	{
		$context = new ExecutorContext($this->createNewLogger());
		$context->setPersonContext($agent);
		$context->setEventPerformer('agent');
		$context->setEventType($event_type);
		$context->setEventMethod($event_method);
		return $context;
	}


	/**
	 * @param Person $user
	 * @param string $event_type
	 * @param string $event_method
	 * @return \Application\DeskPRO\Tickets\ExecutorContext
	 */
	public function createUserExecutorContext(Person $user, $event_type, $event_method)
	{
		$context = new ExecutorContext($this->createNewLogger());
		$context->setPersonContext($user);
		$context->setEventPerformer('user');
		$context->setEventType($event_type);
		$context->setEventMethod($event_method);
		return $context;
	}


	/**
	 * @param string $event_type
	 * @param string $event_method
	 * @return \Application\DeskPRO\Tickets\ExecutorContext
	 */
	public function createSystemExecutorContext($event_type = 'system', $event_method = 'system')
	{
		$context = new ExecutorContext($this->createNewLogger());
		$context->setEventType($event_type);
		$context->setEventMethod($event_method);
		return $context;
	}

	/**
	 * @return Logger
	 */
	protected function createNewLogger()
	{
		$logger = new Logger('tickets');
		$logger->pushHandler(new StreamHandler('php://stdout', 'DEBUG'));
		return $logger;
	}
}