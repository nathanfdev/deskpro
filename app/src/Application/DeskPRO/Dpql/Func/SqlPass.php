<?php

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;

class SqlPass extends AbstractFunc
{
	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		$valuesSql = array();
		$valuesNames = array();
		foreach ($this->_arguments AS $arg) {
			$prepped = $arg->prepare($statement, $section, $stack, $select, $result);
			$valuesSql[] = $prepped->sql();
			$valuesNames[] = $prepped->name();
		}

		$sql = $this->_name . '(' . implode(', ', $valuesSql) . ')';
		return new Prepared($sql, "$this->_name(" . implode(', ', $valuesNames) . ')');
	}
}