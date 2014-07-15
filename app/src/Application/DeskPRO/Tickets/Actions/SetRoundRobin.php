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
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Log\Entry\RoundRobinEntry;
use Application\DeskPRO\Log\Handler\RoundRobinHandler;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Set the assigned agent from Round Robin queue.
 *
 * @option int id   Round Robin id
 */
class SetRoundRobin extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
	protected $logHandler;

	/**
	 * @param DeskproContainer $container
	 */
	public function setContainer(DeskproContainer $container)
	{
		parent::setContainer($container);
		/** @var Connection $conn */
		$conn = $container->get('doctrine.dbal.default_connection');
		$this->logHandler = new RoundRobinHandler($conn);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getOptionsDef()
	{
		$options = new CheckedOptionsArray();
		$options->addRequiredNames('id');
		return $options;
	}

	/**
	 * @return \Application\DeskPRO\EntityRepository\RoundRobin
	 */
	protected function getRep()
	{
		return $this->getContainer()->getEm()->getRepository('DeskPRO:RoundRobin');
	}

	protected function getRoundRobin($id)
	{
		return $this->getRep()->find($id);
	}

	protected function resolve()
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		$context->getLogger()->pushHandler($this->logHandler);

		try {
			$id = $this->getActionOption('id');

			if (!$rr = $this->getRoundRobin($id)) {
				throw new \InvalidArgumentException(sprintf('No Round Robin found with id %d', $id));
			}

			if (!$agent = $this->getRep()->getNextAgent($rr)) {
				throw new \RuntimeException('Unable to assign agent');
			}

			$this->getRep()->updateNextAgent($rr);
			$ticket->agent = $agent;

			$entry = new RoundRobinEntry($rr['id'], $agent['id'], $ticket['id'], 0);
			$context->getLogger()->info($entry, $entry->context());

		} catch (\RuntimeException $e) {
			// todo log error
		} catch (\InvalidArgumentException $e) {
			// todo log error
		}

		$context->getLogger()->popHandler();
	}



	/**
	 * {@inheritDoc}
	 */
	public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
	{
		if (! (int) $this->getContainer()->getSetting('core.round_robin.enabled')) {
			return true;
		}

		if (! $rr = $this->getRoundRobin($this->getActionOption('id'))) {
			return true;
		}

		if ($rr->agents->isEmpty()) {
			return true;
		}

		return false;
	}


	/**
	 * {@inheritDoc}
	 */
	public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
	{
		$id = $this->getActionOption('id');

		if (!$rr = $this->getRoundRobin($id)) {
			return null;
		}

		if (!$agent = $this->getRep()->getNextAgent($rr)) {
			return null;
		}

		if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'assign_agent')) {
			if ($agent['id'] == $person->getId()) {
				if ($person->PermissionsManager->TicketChecker->canModify($ticket, 'assign_self')) {
					return null;
				}
				return array('assign_self');
			}

			return array('assign_agent');
		}

		return null;
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
	{
		$this->applyAction($ticket, $context);
	}
}