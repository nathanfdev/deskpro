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
 * Set the urgency.
 *
 * `mode` can be any of:
 * - set: Sets a specific urgency
 * - add: Adds to urgency
 * - sub: Subtract from urgency
 * - raise: Raises urgency to X if it is lower
 * - lower: Lowers urgency to X if it higher
 *
 * @option int urgency
 * @option int mode
 */
class SetUrgency extends AbstractAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
	const MODE_SET   = 'set';
	const MODE_ADD   = 'add';
	const MODE_SUB   = 'sub';
	const MODE_RAISE = 'raise';
	const MODE_LOWER = 'lower';


	/**
	 * {@inheritDoc}
	 */
	protected function getOptionsDef()
	{
		$options = new CheckedOptionsArray();
		$options->addRequiredNames('mode', 'urgency');
		return $options;
	}


	/**
	 * @param string $mode
	 * @param int $num
	 * @param int $current_urgency
	 * @return int
	 */
	private function getUrgencyResult($mode, $num, $current_urgency)
	{
		switch ($mode) {
			case self::MODE_SET:
				return $num;

			case self::MODE_ADD:
				return min(10, $current_urgency+$num);

			case self::MODE_SUB:
				return max(1, $current_urgency-$num);

			case self::MODE_RAISE:
				if ($current_urgency > $num) {
					return $current_urgency;
				}
				return $num;

			case self::MODE_LOWER:
				if ($current_urgency < $num) {
					return $current_urgency;
				}
				return $num;
		}

		return $current_urgency;
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		$target_urgency = $this->getUrgencyResult(
			$this->getActionOption('mode'),
			$this->getActionOption('urgency'),
			$ticket->urgency
		);

		$ticket->urgency = $target_urgency;
	}


	/**
	 * {@inheritDoc}
	 */
	public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
	{
		$target_urgency = $this->getUrgencyResult(
			$this->getActionOption('mode'),
			$this->getActionOption('urgency'),
			$ticket->urgency
		);

		if ($ticket->urgency == $target_urgency) {
			return true;
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