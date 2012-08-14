<?php

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;
use Application\DeskPRO\Dpql\Exception;

class Percent extends AbstractFunc
{
	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		if (!in_array($section, array('select', 'split', 'group', 'order'))) {
			throw new Exception('PERCENT() may only be used in SELECT, SPLIT BY, GROUP BY, and ORDER BY sections.');
		}

		if (count($this->_arguments) != 1) {
			throw new Exception('PERCENT() can only accept 1 argument');
		}

		$condition = reset($this->_arguments);
		$prepped = $condition->prepare($statement, $section, $stack, $select, $result);

		$sql = 'IF(COUNT(*) > 0, SUM(IF(' . $prepped->sql() . ', 1, 0)) / COUNT(*), 0)';
		return new Prepared($sql, 'PERCENT(' . $prepped->name() . ')');
	}
}