<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Set the category.
 *
 * @option int category_id
 */
class SetCategory extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
	/**
	 * {@inheritDoc}
	 */
	protected function getOptionsDef()
	{
		$options = new CheckedOptionsArray();
		$options->addRequiredNames('category_id');
		return $options;
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		$set_cat_id = $this->getActionOption('category_id');

		if ($set_cat_id) {
			$cat = $this->getContainer()->getTicketCategories()->getSettableById($set_cat_id);
			if (!$cat) {
				return; //invalid
			}
		} else {
			$cat = null;
		}

		$ticket->category = $cat;
	}


	/**
	 * {@inheritDoc}
	 */
	public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
	{
		$set_cat_id    = $this->getActionOption('category_id');
		$ticket_cat_id = $ticket->category ? $ticket->category->id : 0;

		if ($ticket_cat_id == $set_cat_id) {
			return true;
		}

		if ($set_cat_id) {
			$cat = $this->getContainer()->getTicketCategories()->getSettableById($set_cat_id);
			if (!$cat) {
				return true; //invalid
			}
		}

		return false;
	}


	/**
	 * {@inheritDoc}
	 */
	public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
	{
		if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'fields')) {
			return array('fields');
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