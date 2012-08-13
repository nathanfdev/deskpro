<?php

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;

class ToUtc extends AbstractFunc
{
	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		if (count($this->_arguments) != 1) {
			throw new \Exception('TO_UTC() can only accept 1 argument');
		}

		$arg = reset($this->_arguments);

		$argPrepared = $arg->prepare($statement, $section, $stack, $select, $result);

		$tzOffsetSeconds = $statement->getTimezoneOffsetForFunction($stack);
		$interval = ($tzOffsetSeconds ? " - INTERVAL $tzOffsetSeconds SECOND" : '');

		return new Prepared("({$argPrepared->sql()}$interval)", "TO_UTC({$argPrepared->name()})");
	}
}