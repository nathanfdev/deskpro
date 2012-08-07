<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

class BinaryComparison
{
	public $operator;
	public $lhs;
	public $rhs;

	public function __construct($operator, $lhs, $rhs) {
		$this->operator = $operator;
		$this->lhs = $lhs;
		$this->rhs = $rhs;
	}
}