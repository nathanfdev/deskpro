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

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\DependencyInjection\DeskproContainerAwareInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

class ActionComposite implements ActionInterface, DeskproContainerAwareInterface, \Countable, \IteratorAggregate
{
	/**
	 * @var ActionInterface[]
	 */
	private $actions = array();

	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	private $container;

	/**
	 * @param ActionInterface[] $actions
	 */
	public function __construct(array $actions = array())
	{
		$this->setAll($actions);
	}


	/**
	 * @param DeskproContainer $container
	 */
	public function setContainer(DeskproContainer $container)
	{
		$this->container = $container;
	}

		/**
		 * Gets the set container.
		 *
		 * @return DeskproContainer
		 * @throws \RuntimeException When no container has been set yet
		 */
		protected function getContainer()
	{
		if (!$this->container) {
			throw new \RuntimeException("No container has been set");
		}

		return $this->container;
	}


	/**
	 * @param ActionInterface $term
	 */
	public function add(ActionInterface $term)
	{
		$this->actions[] = $term;
	}


	/**
	 * @param ActionInterface[] $actions
	 */
	public function setAll(array $actions)
	{
		$this->actions = array();
		foreach ($actions as $t) {
			$this->add($t);
		}
	}


	/**
	 * @return ActionInterface[]
	 */
	public function getAll()
	{
		return $this->actions;
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		foreach ($this->actions as $a) {
			if ($a instanceof DeskproContainerAwareInterface) {
				$a->setContainer($this->getContainer());
			}
			if ($a instanceof NoopableInterface) {
				if ($a->isNoop($ticket, $context)) {
					continue;
				}
			}

			$a->applyAction($ticket, $context);
		}
	}


	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->actions);
	}


	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->actions);
	}
}