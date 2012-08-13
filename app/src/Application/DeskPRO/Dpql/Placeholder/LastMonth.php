<?php

namespace Application\DeskPRO\Dpql\Placeholder;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;
use Application\DeskPRO\Dpql\Statement\Part\AbstractPart;

class LastMonth extends AbstractDateRange
{
	protected function _getDateRange()
	{
		$tz = new \DateTimeZone(App::getCurrentPerson()->getTimezone());
		$date = new \DateTime('now', $tz);

		$endDate = new \DateTime("$thisMonth-01", $tz);
		$endDate->modify('-1 day');

		$endDateValue = $endDate->format('Y-m-d');
		$startDateValue = $endDate->format('Y-m') . '-01';

		return array("$startDateValue to $endDateValue", "$startDateValue 00:00:00", "$endDateValue 23:59:59");
	}
}