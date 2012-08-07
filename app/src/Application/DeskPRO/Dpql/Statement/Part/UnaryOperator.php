<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

class UnaryOperator
{
	public $operator;
	public $value;

	public function __construct($operator, $value) {
		$this->operator = $operator;
		$this->value= $value;
	}
}