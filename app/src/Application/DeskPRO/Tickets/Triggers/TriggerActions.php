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

namespace Application\DeskPRO\Tickets\Triggers;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\DependencyInjection\DeskproContainerAwareInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\ActionComposite;
use Application\DeskPRO\Tickets\Actions\ActionDefinitionInterface;
use Application\DeskPRO\Tickets\Actions\ActionInterface;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Kernel\KernelErrorHandler;
use Orb\Types\JsonObjectSerializable;

/**
 * This is a wrapper around an ActionComposite that is able to serialize.
 * Used as the serialized object in TicketTrigger records.
 *
 * While any `ActionInterface` can be used with the trigger system, we can only actually
 * *save* the term to the db if it also implements the standard ActionDefinitionInterface which defines
 * a standard interface for getting a term name and options (so we can recreate a term object again).
 */
class TriggerActions implements \Serializable, ActionInterface, DeskproContainerAwareInterface, JsonObjectSerializable, \Countable
{
	/**
	 * @var ActionComposite
	 */
	private $actions;

	/**
	 * @var ActionFactory
	 */
	private $action_factory;

	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	private $container;

	public function __construct()
	{
		$this->actions = new ActionComposite();
		$this->action_factory = new ActionFactory();
	}


	/**
	 * @param ActionInterface $action
	 * @throws \InvalidArgumentException
	 */
	public function addAction(ActionInterface $action)
	{
		if (!($action instanceof ActionDefinitionInterface)) {
			$class_name = get_class($action);
			throw new \InvalidArgumentException("TriggerActions can only manage terms terms that implement ActionDefinitionInterface. Invalid class: $class_name");
		}
		$this->actions->add($action);
	}


	/**
	 * @param array $action_info
	 * @throws \InvalidArgumentException
	 */
	public function addActionFromArray(array $action_info)
	{
		$action = $this->action_factory->createFromArray($action_info);
		$this->addAction($action);
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
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		if ($this->container && $this->actions instanceof DeskproContainerAwareInterface) {
			$this->actions->setContainer($this->container);
		}

		$this->actions->applyAction($ticket, $context);
	}


	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->actions);
	}


	/**
	 * @return array
	 */
	public function exportToArray()
	{
		$data = array();

		$data['version']  = 1;
		$data['actions'] = array();
		foreach ($this->actions->getAll() as $actions) {
			if (!($actions instanceof ActionDefinitionInterface)) {
				continue;
			}

			if (strpos(get_class($actions), 'Application\\DeskPRO\\Tickets\\Actions\\') === 0) {
				$data['actions'][] = array(
					'type'    => $actions->getActionType(),
					'options' => $actions->getActionOptions()->all()
				);
			} else {
				$data['actions'][] = array(
					'type'       => $actions->getActionType(),
					'type_class' => get_class($actions),
					'options'    => $actions->getActionOptions()->all()
				);
			}
		}

		return $data;
	}


	/**
	 * @return string
	 */
	public function exportToJson()
	{
		return json_encode($this->exportToArray());
	}


	/**
	 * @param array $data
	 */
	public function importFromArray(array $data)
	{
		foreach ($data['actions'] as $action_info) {
			$this->addActionFromArray($action_info);
		}
	}


	/**
	 * @return array
	 */
	public function serializeJsonArray()
	{
		return $this->exportToArray();
	}


	/**
	 * @param array $data
	 * @return TriggerActions
	 */
	public static function unserializeJsonArray(array $data)
	{
		$obj = new self();
		foreach ($data['actions'] as $action_info) {
			try {
				$obj->addActionFromArray($action_info);
			} catch (\Exception $e) {
				if (!empty($action_info['type'])) {
					KernelErrorHandler::logException($e, false, md5('action_' . $action_info['type']));
				}
			}
		}
		return $obj;
	}


	/**
	 * @return string
	 */
	public function serialize()
	{
		return $this->exportToJson();
	}


	/**
	 * @param string $data
	 */
	public function unserialize($data)
	{
		$data = json_decode($data, true);

		$this->__construct();

		foreach ($data['actions'] as $action_info) {
			try {
				$this->addActionFromArray($action_info);
			} catch (\Exception $e) {
				if (!empty($action_info['type'])) {
					KernelErrorHandler::logException($e, false, md5('action_' . $action_info['type']));
				}
			}
		}
	}
}