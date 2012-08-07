<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

class Alias
{
	public $value;
	public $alias;

	public function __construct($value, $alias) {
		$this->value = $value;
		$this->alias = $alias;
	}
}