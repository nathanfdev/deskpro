<?php

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;

class Y extends AbstractFunc
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

				$result->addGroupYColumn($groupBy, $id);
			}
		}

		return false;
	}
}