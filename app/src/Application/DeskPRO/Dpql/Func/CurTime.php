<?php

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;

class CurTime extends AbstractFunc
{
	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		if (count($this->_arguments)) {
			throw new \Exception('CURTIME() can only accept 0 arguments');
		}

		$tzOffsetSeconds = App::getCurrentPerson()->getTimezoneOffset() * 3600;
		$interval = ($tzOffsetSeconds ? " + INTERVAL $tzOffsetSeconds SECOND" : '');

		$sql = "TIME(UTC_TIMESTAMP()$interval)";

		return new Prepared($sql, 'CURTIME()');
	}
}