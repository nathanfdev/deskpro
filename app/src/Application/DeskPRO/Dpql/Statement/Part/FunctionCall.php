<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;

class FunctionCall extends AbstractPart
{
	public $name;
	public $arguments;

	public function __construct($name, array $arguments = array())
	{
		$this->name = $name;
		$this->arguments = $arguments;
	}

	public function toSql(Display $statement, $section, array $stack)
	{
		$childStack = $this->getChildStack($stack);

		$argOutput = array();
		foreach ($this->arguments AS $arg) {
			$argOutput[] = $arg->toSql($statement, $section, $childStack);
		}

		return $this->name . '(' . implode(', ', $argOutput) . ')';
	}
}