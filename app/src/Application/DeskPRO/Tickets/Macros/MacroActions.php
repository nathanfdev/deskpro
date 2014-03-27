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

namespace Application\DeskPRO\Tickets\Macros;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\ActionContext;
use Application\DeskPRO\Tickets\Actions\ActionDefinitionInterface;
use Application\DeskPRO\Tickets\Actions\MacroActionComposite;
use Application\DeskPRO\Tickets\Actions\MacroActionInterface;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

/**
 * This is a wrapper around an ActionComposite that is able to serialize.
 * Used as the serialized object in TicketMacro records.
 *
 * While any `MacroActionInterface` can be used with the macro system, we can only actually
 * *save* the term to the db if it also implements the standard ActionDefinitionInterface which defines
 * a standard interface for getting a term name and options (so we can recreate a term object again).
 */
class MacroActions implements \Serializable, MacroActionInterface
{
	/**
	 * @var ActionComposite
	 */
	private $actions;

	public function __construct()
	{
		$this->actions = new MacroActionComposite();
	}


	/**
	 * @param MacroActionInterface $action
	 * @throws \InvalidArgumentException
	 */
	public function addAction(MacroActionInterface $action)
	{
		if (!($action instanceof ActionDefinitionInterface)) {
			$class_name = get_class($action);
			throw new \InvalidArgumentException("MacroActions can only manage terms terms that implement ActionDefinitionInterface. Invalid class: $class_name");
		}
		$this->actions->add($action);
	}


	/**
	 * @param array $action_info
	 * @throws \InvalidArgumentException
	 */
	public function addActionFromArray(array $action_info)
	{
		$class_name = "Application\\DeskPRO\\Tickets\\Actions\\{$action_info['type']}";
		if (!class_exists($class_name)) {
			throw new \InvalidArgumentException("Unknown action {$action_info['type']} (could not locate class: $class_name)");
		}

		$action = new $class_name($action_info['options']);
		$this->addAction($action);
	}

	/**
	 * Return an array of macros that the user does not have permission to use.
	 * An empty array means there are no permission errors.
	 *
	 * @param Person $person
	 * @param Ticket $ticket
	 * @param ExecutorContextInterface $context
	 * @return array
	 */
	public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
	{
		return $this->actions->getMacroPermissionErrors($person, $ticket, $context);
	}


	/**
	 * @param Person $person
	 * @param Ticket $ticket
	 * @param ExecutorContextInterface $context
	 * @return void
	 */
	public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
	{
		$this->actions->applyMacro($person, $ticket, $context);
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

			$data['actions'][] = array(
				'type'    => $actions->getActionType(),
				'options' => $actions->getActionOptions()->all()
			);
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
					KernelErrorHandler::logException($e, false, md5('macro_' . $action_info['type']));
				}
			}
		}
	}
}