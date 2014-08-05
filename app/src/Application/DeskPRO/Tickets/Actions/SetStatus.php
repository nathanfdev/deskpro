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

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Set the status.
 *
 * @option string status
 */
class SetStatus extends AbstractAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
	/**
	 * {@inheritDoc}
	 */
	protected function getOptionsDef()
	{
		$options = new CheckedOptionsArray();
		$options->addRequiredNames('status');

		$me = $this;
		$options->addCallbackCheckedOption('status', function($v) use ($me) {
			return $me->isValidStatus($v);
		});
		return $options;
	}


	/**
	 * @param string $status
	 * @return bool
	 */
	public function isValidStatus($status)
	{
		static $valid_statuses = array(
			'awaiting_agent', 'awaiting_user', 'resolved', 'closed',
			'hidden.spam', 'hidden.deleted', 'hidden.temp', 'hidden.validating'
		);

		return in_array($status, $valid_statuses);
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		$set_status = $this->getActionOption('status');
		if (!$this->isValidStatus($set_status)) {
			return;
		}

		$ticket->setStatus($set_status);
		$context->getLogger()->debug("[SetStatus] Setting status $set_status");
	}


	/**
	 * {@inheritDoc}
	 */
	public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
	{
		$set_status = $this->getActionOption('status');
		if ($ticket->getStatusCode() == $set_status || !$this->isValidStatus($set_status)) {
			return true;
		}

		return false;
	}


	/**
	 * {@inheritDoc}
	 */
	public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
	{
		$set_status = $this->getActionOption('status');
		if (($set_status == 'hidden.deleted' || $set_status == 'hidden.spam') && !$person->PermissionsManager->TicketChecker->canDelete($ticket)) {
			return array('delete');
		}
		if ($set_status == 'awaiting_agent' && !$person->PermissionsManager->TicketChecker->canModify($ticket, 'set_awaiting_agent')) {
			return array('set_awaiting_agent');
		}
		if ($set_status == 'awaiting_user' && !$person->PermissionsManager->TicketChecker->canModify($ticket, 'set_awaiting_user')) {
			return array('set_awaiting_user');
		}
		if ($set_status == 'resolved' && !$person->PermissionsManager->TicketChecker->canModify($ticket, 'set_resolved')) {
			return array('set_resolved');
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