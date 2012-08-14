<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;

class String extends AbstractPart
{
	public $string;

	public function __construct($string)
	{
		$this->string = $string;
	}

	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		return new Prepared($select->escapeForSql($this->string), $this->string);
	}

	public function toDpql(Display $statement, $section, array $stack)
	{
		return $statement->quoteDpqlString($this->string);
	}
}