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

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\Arrays;
use Orb\Util\CheckedOptionsArray;
use Orb\Util\OptionsArray;
use Orb\Util\Util;
use Orb\Util\WorkHoursSet;

/**
 * Checks when ticket was created.
 *
 * @option int date1
 * @option int date2
 * @option string date1_relative
 * @option string date2_relative
 */
class CheckWorkingHours extends AbstractTriggerTerm
{
	/**
	 * {@inheritDoc}
	 */
	protected function getOptionsDef()
	{
		$options = new CheckedOptionsArray();
		$options->addValidNames('set_name');
		$options->addValidNames('working_hours');
		return $options;
	}


	/**
	 * {@inheritDoc}
	 */
	public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
	{
		$options = $this->getTermOptions();

		if ($options->get('set_name') == 'default') {
			$context->getLogger()->debug('[CheckWorkingHours] Default hours');
			//todo refactor terms so they can get passed a container
			$working_hours = App::getSetting('core_tickets.work_hours');
			if ($working_hours && !is_array($working_hours)) {
				$working_hours = @unserialize($working_hours);
			}
		} else {
			$context->getLogger()->debug('[CheckWorkingHours] Custom hours');
			$working_hours = $options->get('working_hours');
		}

		if (!$working_hours) {
			return false;
		}

		$working_hours = Arrays::removeEmptyArray($working_hours);
		$working_hours = Arrays::removeNull($working_hours);
		$working_hours = Arrays::removeEmptyString($working_hours);

		$working_hours = new OptionsArray($working_hours);
		$wh = new WorkHoursSet(
			$working_hours->get('start_hour', 9) * 3600 + $working_hours->get('start_minute', 0) * 60,
			$working_hours->get('end_hour', 18) * 3600 + $working_hours->get('end_minute', 0) * 60,
			$working_hours->get('work_days', array(false, true, true, true, true, true, false)),
			$working_hours->get('timezone', $working_hours->get('timezone', 'UTC')),
			$working_hours->get('holidays', array())
		);

		$context->getLogger()->debug('[CheckWorkingHours] Config: ' . Arrays::implodeTemplate($working_hours->all(), '{KEY}: {VAL}, '));

		try {
			$tz = new \DateTimeZone($working_hours->get('timezone', 'UTC'));
			if (!$tz) {
				return false;
			}
		} catch (\Exception $e) {
			return false;
		}

		$now = new \DateTime('now', $tz);

		$is_in_workday = $wh->isInWorkDay($now);
		if ($is_in_workday) {
			$context->getLogger()->debug('[CheckWorkingHours] IS in working hours');
		} else {
			$context->getLogger()->debug('[CheckWorkingHours] IS NOT in working hours');
		}

		if ('is' === $this->getTermOperator() && $is_in_workday) {
			return true;
		}

		if ('not' === $this->getTermOperator() && !$is_in_workday) {
			return true;
		}

		return false;
	}
}