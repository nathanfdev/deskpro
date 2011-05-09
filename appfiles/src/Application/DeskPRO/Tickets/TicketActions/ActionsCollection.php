<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\Tickets\TicketActions\ActionInterface;
use Application\DeskPRO\Tickets\TicketActions\CollectionModifierInterface;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;

/**
 * A collection of ticket actions
 */
class ActionsCollection
{
	/**
	 * @var Application\DeskPRO\Tickets\TicketActions\ActionInterface[]
	 */
	protected $actions = array();

	public function add($action_or_modifier)
	{
		if ($action_or_modifier instanceof ActionInterface) {
			$this->addAction($action_or_modifier);
		} elseif ($action_or_modifier instanceof CollectionModifierInterface) {
			$this->applyCollectionModifier($action_or_modifier);
		}
	}

	
	/**
	 * Add a new action
	 *
	 * @param \Application\DeskPRO\Tickets\TicketActions\ActionInterface $action
	 */
	public function addAction(ActionInterface $action)
	{
		$name = get_class($action);

		if (isset($this->actions[$name])) {
			$old_action = $this->actions[$name];
			$action = $old_action->merge($action);
		}

		$this->actions[$name] = $action;
	}


	/**
	 * Apply a collection modifier
	 *
	 * @param \Application\DeskPRO\Tickets\TicketActions\CollectionModifierInterface $modifier
	 */
	public function applyCollectionModifier(CollectionModifierInterface $modifier)
	{
		$modifier->modifyCollection($this);
	}


	/**
	 * Check if a certain action type is set
	 *
	 * @return bool
	 */
	public function hasActionType($name)
	{
		if (strpos($name, '\\') === false) {
			$name = 'Application\\DeskPRO\\Tickets\\TicketActions\\' . $name;
		}

		return isset($this->actions[$name]);
	}


	/**
	 * Get a set action by name
	 *
	 * @throws \InvalidArgumentException When the action doesnt exist
	 * @param  $name
	 * @return \Application\DeskPRO\Tickets\TicketActions\ActionInterface
	 */
	public function getActionType($name)
	{
		if (strpos($name, '\\') === false) {
			$name = 'Application\\DeskPRO\\Tickets\\TicketActions\\' . $name;
		}
		
		if (!$this->hasActionType($name)) {
			throw new \InvalidArgumentException("No action type `$name` exists");
		}

		return $this->actions[$name];
	}

	
	/**
	 * Remove an action type from the collection, and return it
	 *
	 * @throws \InvalidArgumentException When action doesnt exist
	 * @param  string $name
	 * @return Application\DeskPRO\Tickets\TicketActions\ActionInterface
	 */
	public function removeActionType($name)
	{
		if (strpos($name, '\\') === false) {
			$name = 'Application\\DeskPRO\\Tickets\\TicketActions\\' . $name;
		}

		if (!$this->hasActionType($name)) {
			throw new \InvalidArgumentException("No action type `$name` exists");
		}

		$action = $this->actions[$name];
		unset($this->actions[$name]);

		return $action;
	}


	/**
	 * Get an array of set action types
	 *
	 * @return array
	 */
	public function getActionTypeNames()
	{
		return array_keys($this->actions);
	}


	/**
	 * Get an array of ticket actions
	 * 
	 * @return array
	 */
	public function getActions()
	{
		return $actions;
	}

	
	/**
	 * Apply actions in this collection to $ticket, using $person_context as
	 * the context on actions that require it.
	 * 
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @param \Application\DeskPRO\Entity\Person $person_context
	 */
	public function apply(Ticket $ticket, Person $person_context)
	{
		foreach ($this->actions as $action) {
			if ($action instanceof PersonContextInterface) {
				$action->setPersonContext($person_context);
			}

			$action->apply($ticket);
		}
	}
}