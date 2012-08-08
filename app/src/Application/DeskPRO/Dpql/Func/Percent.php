<?php

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;

class Percent extends AbstractFunc
{
	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		$condition = reset($this->_arguments);
		$conditionSql = $condition->prepare($statement, $section, $stack, $select, $result);
		return 'IF(COUNT(*) > 0, SUM(IF(' . $conditionSql . ', 1, 0)) / COUNT(*), 0)';
	}
}