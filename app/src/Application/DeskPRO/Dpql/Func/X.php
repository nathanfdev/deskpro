<?php

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;

class X extends AbstractFunc
{
	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		if ($section != 'group') {
			throw new \Exception('X() may only be used in GROUP BY.');
		}
		if ($stack) {
			throw new \Exception('X() may only be used at the top-level.');
		}

		foreach ($this->_arguments AS $arg) {
			$groupBy = $arg->prepare($statement, $section, $stack, $select, $result);
			if ($groupBy->hasValue()) {
				$id = $select->addSelectField($groupBy->printed());
				$select->addGroupBy($groupBy->sql());

				$result->addGroupXColumn($groupBy->name(), $id);
			}
		}

		return new Prepared(false);
	}
}