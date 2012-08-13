<?php

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;

class Printable extends AbstractFunc
{
	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		if (!in_array($section, array('split', 'group'))) {
			throw new \Exception('PRINT() may only be used in SPLIT BY and GROUP BY sections.');
		}

		if (count($this->_arguments) != 2) {
			throw new \Exception('PRINT() can only accept 2 arguments');
		}

		$sql = reset($this->_arguments);
		$print = next($this->_arguments);

		$printPrepped = $print->prepare($statement, $section, $stack, $select, $result);
		$sqlPrepped = $sql->prepare($statement, $section, $stack, $select, $result);

		return new Prepared($sqlPrepped->sql(), $printPrepped->name(), $printPrepped->printed());
	}
}