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
 * Checks if now is within a time of day.
 *
 * `time1` and `time2` should be a time in 24-hour format: 08:00 for 8 o'clock.
 * `time2` is only used if the op is `between` and we need an end-time comparison.
 *
 * @option string time1
 * @option string time2
 * @option string tz    The timezone to test in
 * @option string var   The value date to test (defaults to now). Also available: 'date_created'
 * @option \DateTime test_date  When specified, this date is used instead of now
 */
class CheckTimeOfDay extends AbstractTriggerTerm
{
	/**
	 * {@inheritDoc}
	 */
	protected function getOptionsDef()
	{
		$options = new CheckedOptionsArray();
		$options->addRequiredNames('time1', 'tz');
		$options->addValidNames('time1', 'time2', 'var', 'test_date');
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

		$fn_check = function($time, $op) use ($now) {
			list($hour, $min) = explode(':', $time);
			$hour = (int)$hour;
			$min  = (int)$min;

			$now_hour = (int)$now->format('G');
			$now_min  = (int)$now->format('i');

			if ($op == 'gt' || $op == 'gte') {
				if ($hour == $now_hour) {
					return $min <= $now_min;
				} else {
					return $hour < $now_hour;
				}
			} else {
				if ($hour == $now_hour) {
					return $min >= $now_min;
				} else {
					return $hour > $now_hour;
				}
			}
		};

		switch ($op) {
			case 'gt':
			case 'gte':
				return $fn_check($options->get('time1'), 'gt');

			case 'lt':
			case 'lte':
				return $fn_check($options->get('time1'), 'lt');

			case 'between':
				return $fn_check($options->get('time1'), 'gt') && $fn_check($options->get('time2'), 'lt');

			default:
				return false;
		}
	}
}