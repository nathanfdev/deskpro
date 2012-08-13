<?php

namespace Application\DeskPRO\Dpql\Placeholder;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;
use Application\DeskPRO\Dpql\Statement\Part\AbstractPart;

class Yesterday extends AbstractDateRange
{
	protected function _getDateRange()
	{
		$tz = App::getCurrentPerson()->getTimezone();
		$date = new \DateTime('-1 day', new \DateTimeZone($tz));
		$yesterday = $date->format('Y-m-d');

		return array($yesterday, "$yesterday 00:00:00", "$yesterday 23:59:59");
	}
}