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
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;
use Orb\Util\Util;

/**
 * Checks when ticket was created.
 *
 * @option int date1
 * @option int date2
 * @option string date1_relative
 * @option string date2_relative
 */
class CheckDateCreated extends AbstractTriggerTerm
{
	/**
	 * {@inheritDoc}
	 */
	protected function getOptionsDef()
	{
		$options = new CheckedOptionsArray();
		$options->addValidNames('date1', 'date2', 'date1_relative', 'date2_relative', 'date1_relative_type', 'date2_relative_type');
		return $options;
	}


	/**
	 * {@inheritDoc}
	 */
	public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
	{
		$opts = $this->getTermOptions();

		$date1 = null;
		$date2 = null;

		try {
			if ($opts['date1']) {
				$date1 = new \DateTime('@' . $opts['date1']);
			} else if ($opts['date1_relative']) {
				$date1 = new \DateTime('@' . @strtotime('-' . $opts['date1_relative'] . ' ' . $opts->get('date1_relative_type', 'days')));
			} else {
				$date1 = null;
			}
		} catch (\Exception $e) {
			$date1 = null;
		}

		try {
			if ($opts['date2']) {
				$date2 = new \DateTime('@' . $opts['date2']);
			} else if ($opts['date2_relative']) {
				$date2 = new \DateTime('@' . @strtotime('-' . $opts['date2_relative'] . ' ' . $opts->get('date2_relative_type', 'days')));
			} else {
				$date2 = null;
			}
		} catch (\Exception $e) {
			$date2 = null;
		}

		switch ($this->getTermOperator()) {
			case 'lt':
			case 'lte':
			case 'gt':
			case 'gte':
				if (!$date1 && !$date2) {
					return false;
				}

				$d = Util::coalesce($date1, $date2);
				return $this->isDateMatch($ticket, $context, 'date_created', $d);

			case 'between':
				if (!$date1 || !$date2) {
					return false;
				}

				return $this->isDateRangeMatch($ticket, $context, 'date_created', $date1, $date2);

			default:
				return false;
		}
	}
}