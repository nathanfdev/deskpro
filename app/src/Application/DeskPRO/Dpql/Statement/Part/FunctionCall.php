<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

class FunctionCall
{
	public $name;
	public $arguments;

	public function __construct($name, array $arguments = array()) {
		$this->name = $name;
		$this->arguments = $arguments;
	}
}