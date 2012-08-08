<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;

class Placeholder extends AbstractPart
{
	public $name;

	public function __construct($name)
	{
		$this->name = $name;
	}

	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		return $this->escapeForSql('%' . $this->name . '%');
	}
}