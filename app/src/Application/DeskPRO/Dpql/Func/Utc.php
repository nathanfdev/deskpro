<?php

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;
use Application\DeskPRO\Dpql\Exception;

class Utc extends AbstractFunc
{
	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		if (count($this->_arguments) != 1) {
			throw new Exception('UTC() can only accept 1 argument');
		}

		$arg = reset($this->_arguments);

		$prepared = $arg->prepare($statement, $section, $stack, $select, $result);
		$prepared->setName('UTC(' . $prepared->name() . ')');

		return $prepared;
	}
}