<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;

class NullValue extends AbstractPart
{
	public function toSql(Display $statement, $section, array $stack)
	{
		return 'NULL';
	}
}