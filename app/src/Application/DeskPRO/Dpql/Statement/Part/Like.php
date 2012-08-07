<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

class Like
{
	public $lhs;
	public $rhs;
	public $positive;

	public function __construct($lhs, $rhs, $positive = true) {
		$this->lhs = $lhs;
		$this->rhs = $rhs;
		$this->positive = $positive;
	}
}