<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

class Placeholder
{
	public $name;

	public function __construct($name) {
		$this->name = $name;
	}
}