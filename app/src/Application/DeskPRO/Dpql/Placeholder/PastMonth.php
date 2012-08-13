<?php

namespace Application\DeskPRO\Dpql\Placeholder;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;
use Application\DeskPRO\Dpql\Statement\Part\AbstractPart;

class PastMonth extends AbstractDateRange
{
	protected function _getDateRange()
	{
		$tz = App::getCurrentPerson()->getTimezone();
		$date = new \DateTime('now', new \DateTimeZone($tz));

		$now = $date->format('Y-m-d H:i:s');
		$today = $date->format('Y-m-d');

		$date->modify('-1 month');
		$beginning = $date->format('Y-m-d');

		return array("$begining to $today", "$beginning 00:00:00", $now);
	}
}