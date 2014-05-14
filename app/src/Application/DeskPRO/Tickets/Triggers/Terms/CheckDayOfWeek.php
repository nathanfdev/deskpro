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

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Checks if now is a day of week
 *
 * `days` is an int where 1=Monday, 7=Sunday (ISO 8601).
 *
 * @option int[] days   The days to test for
 * @option string tz    The timezone to test in
 * @option string var   The value date to test (defaults to now). Also available: 'date_created'
 * @option \DateTime test_date  When specified, this date is used instead of now
 */
class CheckDayOfWeek extends AbstractTriggerTerm
{
	/**
	 * {@inheritDoc}
	 */
	protected function getOptionsDef()
	{
		$options = new CheckedOptionsArray();
		$options->addRequiredNames('days', 'tz');
		$options->addValidNames('var', 'test_date');
		$options->addCallbackCheckedOption('var', function($v) {
			return ($v == 'now' || $v == 'date_created' || $v === null);
		});
		return $options;
	}


	/**
	 * {@inheritDoc}
	 */
	public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
	{
		$op = $this->getTermOperator();
		$options = $this->getTermOptions();

		try {
			$tz = new \DateTimeZone($options->get('tz', 'UTC'));
		} catch (\Exception $e) {
			$context->getLogger()->warn("[CheckDayOfWeek] Invalid timezone: {$options->get('tz')}");
			return false;
		}

		if ($options->has('test_date')) {
			$now = $options->get('test_date');
		} else {
			$var = $options->get('var', 'now');
			switch ($var) {
				case 'now':
					$now = new \DateTime('now', $tz);
					break;
				case 'date_created':
					$now = $ticket->date_created ?: new \DateTime('now');
					$now->setTimezone($tz);
					break;
				default:
					throw new \InvalidArgumentException("Unknown var type: $var");
			}
		}

		$days = $options->get('days');
		if (!is_array($days)) {
			$days = array($days);
		}
		$days = array_map(function($d) { return (int)$d; }, $days);

		$is_match = in_array((int)$now->format('N'), $days);

		if ($is_match) {
			if ($op == 'is') return true;
			else return false;
		} else {
			if ($op == 'is') return false;
			else return false;
		}
	}
}