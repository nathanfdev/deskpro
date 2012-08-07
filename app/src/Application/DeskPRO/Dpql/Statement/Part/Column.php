<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;

class Column extends AbstractPart
{
	public $table;
	public $field;

	public function __construct($table, $field)
	{
		$this->table = $table;
		$this->field = $field;
	}

	public function toSql(Display $statement, $section, array $stack)
	{
		return "`$this->table`.`$this->field`";
	}
}