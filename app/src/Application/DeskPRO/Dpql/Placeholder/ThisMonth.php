<?php

namespace Application\DeskPRO\Dpql\Placeholder;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;
use Application\DeskPRO\Dpql\Statement\Part\AbstractPart;

class ThisMonth extends AbstractDateRange
{
	protected function _getDateRange()
	{
		$tz = new \DateTimeZone(App::getCurrentPerson()->getTimezone());
		$date = new \DateTime('now', $tz);

		$start = $date->format('Y-m');

		$endDate = new \DateTime("$start-01", $tz);
		$endDate->modify('+1 month')->modify('-1 day');

		$endDateValue = $endDate->format('Y-m-d');

		return array("$start-01 to $endDateValue", "$start-01 00:00:00", "$endDateValue 23:59:59");
	}
}