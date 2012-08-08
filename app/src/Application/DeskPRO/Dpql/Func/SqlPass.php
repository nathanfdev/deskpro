<?php

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;

class SqlPass extends AbstractFunc
{
	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		$argOutput = array();
		foreach ($this->_arguments AS $arg) {
			$argOutput[] = $arg->prepare($statement, $section, $stack, $select, $result);
		}

		return $this->_name . '(' . implode(', ', $argOutput) . ')';
	}
}