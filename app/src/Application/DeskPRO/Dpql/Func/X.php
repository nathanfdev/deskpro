<?php

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;

class X extends AbstractFunc
{
	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		foreach ($this->_arguments AS $arg) {
			$groupBy = $arg->prepare($statement, $section, $stack, $select, $result);
			if ($statement->isSqlValue($groupBy)) {
				$id = $select->addSelectField($groupBy);
				$select->addGroupBy($groupBy);

				$result->addGroupXColumn($groupBy, $id);
			}
		}

		return false;
	}
}