<?php

namespace Application\DeskPRO\Dpql\Placeholder;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;
use Application\DeskPRO\Dpql\Statement\Part\AbstractPart;

class Tomorrow extends AbstractDateRange
{
	protected function _getDateRange()
	{
		$tz = App::getCurrentPerson()->getTimezone();
		$date = new \DateTime('+1 day', new \DateTimeZone($tz));

		$day = $date->format('Y-m-d');

		return array($day, "$day 00:00:00", "$day 23:59:59");
	}
}