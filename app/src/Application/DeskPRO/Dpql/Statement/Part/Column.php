<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;

class Column extends AbstractPart
{
	public $table;
	public $field;

	public function __construct($table, $field)
	{
		$this->table = $table;
		$this->field = $field;
	}

	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		return "`$this->table`.`$this->field`";
	}
}