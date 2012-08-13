<?php

namespace Application\DeskPRO\Dpql\Placeholder;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;
use Application\DeskPRO\Dpql\Statement\Part\AbstractPart;

class LastWeek extends AbstractDateRange
{
	protected function _getDateRange()
	{
		$person = App::getCurrentPerson();
		$tz = new \DateTimeZone($person->getTimezone());
		$date = new \DateTime('now', $tz);

		// find start of this week
		$currentDayOfWeek = $date->format('N');
		$startAdjust = $currentDayOfWeek - $person->getStartOfWeek();

		if ($startAdjust) {
			if ($startAdjust > 0) {
				$date->modify('-' . $startAdjust . ' days');
			} else {
				$date->modify('-' . (7 + $startAdjust) . ' days');
			}
		}

		// move to beginning of previous week
		$date->modify('-7 days');

		$start = $date->format('Y-m-d');

		$date->modify('+6 days'); // 7 days will take us to the next start of the week
		$end = $date->format('Y-m-d');

		return array("$start to $end", "$start 00:00:00", "$end 23:59:59");
	}
}