<?php

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;

class CurDate extends AbstractFunc
{
	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		if (count($this->_arguments)) {
			throw new \Exception('CURDATE() can only accept 0 arguments');
		}

		$tzOffsetSeconds = $statement->getTimezoneOffsetForFunction($stack);
		$interval = ($tzOffsetSeconds ? " + INTERVAL $tzOffsetSeconds SECOND" : '');

		$sql = "DATE(UTC_TIMESTAMP()$interval)";

		return new Prepared($sql, 'CURDATE()');
	}
}