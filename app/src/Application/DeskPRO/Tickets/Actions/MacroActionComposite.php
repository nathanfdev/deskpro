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

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

class MacroActionComposite implements MacroActionInterface
{
	/**
	 * @var MacroActionInterface[]
	 */
	private $actions = array();

	/**
	 * @param MacroActionInterface[] $actions
	 */
	public function __construct(array $actions = array())
	{
		$this->setAll($actions);
	}


	/**
	 * @param MacroActionInterface $term
	 */
	public function add(MacroActionInterface $term)
	{
		$this->actions[] = $term;
	}


	/**
	 * @param MacroActionInterface[] $actions
	 */
	public function setAll(array $actions)
	{
		$this->actions = array();
		foreach ($actions as $t) {
			$this->add($t);
		}
	}


	/**
	 * @return MacroActionInterface[]
	 */
	public function getAll()
	{
		return $this->actions;
	}


	/**
	 * {@inheritDoc}
	 */
	public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
	{
		$errors = array();

		foreach ($this->actions as $act) {
			$errors = array_merge($errors, $act->getMacroPermissionErrors($person, $ticket, $context));
		}

		return $errors;
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
	{
		foreach ($this->actions as $act) {
			$act->applyMacro($person, $ticket, $context);
		}
	}
}