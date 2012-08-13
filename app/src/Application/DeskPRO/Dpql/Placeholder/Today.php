<?php

namespace Application\DeskPRO\Dpql\Placeholder;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;
use Application\DeskPRO\Dpql\Statement\Part\AbstractPart;

class Today extends AbstractDateRange
{
	protected function _getDateRange()
	{
		$tz = App::getCurrentPerson()->getTimezone();
		$date = new \DateTime('now', new \DateTimeZone($tz));
		$today = $date->format('Y-m-d');

		return array($today, "$today 00:00:00", "$today 23:59:59");
	}
}