<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

class Column
{
	public $table;
	public $field;

	public function __construct($table, $field) {
		$this->table = $table;
		$this->field = $field;
	}
}