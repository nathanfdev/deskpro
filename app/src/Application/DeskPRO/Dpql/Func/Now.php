<?php

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;
use Application\DeskPRO\Dpql\Exception;

class Now extends AbstractFunc
{
	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		if (count($this->_arguments)) {
			throw new Exception('NOW() can only accept 0 arguments');
		}

		$tzOffsetSeconds = $statement->getTimezoneOffsetForFunction($stack);
		$interval = ($tzOffsetSeconds ? " + INTERVAL $tzOffsetSeconds SECOND" : '');

		$sql = "(UTC_TIMESTAMP()$interval)";

		return new Prepared($sql, 'NOW()');
	}
}