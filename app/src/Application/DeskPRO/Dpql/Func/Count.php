<?php

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;

class Count extends AbstractFunc
{
	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		if (!$this->_arguments) {
			return 'COUNT(*)';
		}

		$condition = reset($this->_arguments);
		return 'SUM(IF(' . $condition->prepare($statement, $section, $stack, $select, $result) . ', 1, 0))';
	}
}