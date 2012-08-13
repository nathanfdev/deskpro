<?php

namespace Application\DeskPRO\Dpql\Placeholder;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;
use Application\DeskPRO\Dpql\Statement\Part\AbstractPart;

class ThisYear extends AbstractDateRange
{
	protected function _getDateRange()
	{
		$tz = App::getCurrentPerson()->getTimezone();
		$date = new \DateTime('now', new \DateTimeZone($tz));

		$year = $date->format('Y');

		return array($year, "$year-01-01 00:00:00", "$year-12-31 23:59:59");
	}
}