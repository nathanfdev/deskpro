<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

class In
{
	public $lhs;
	public $values;
	public $positive;

	public function __construct($lhs, array $values, $positive = true) {
		$this->lhs = $lhs;
		$this->values = $values;
		$this->positive = $positive;
	}
}