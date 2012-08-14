<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;

class NullValue extends AbstractPart
{
	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		return new Prepared('NULL', 'NULL');
	}

	public function toDpql(Display $statement, $section, array $stack)
	{
		return 'NULL';
	}
}