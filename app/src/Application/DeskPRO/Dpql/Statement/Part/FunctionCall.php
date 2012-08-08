<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Func\AbstractFunc;

class FunctionCall extends AbstractPart
{
	public $name;
	public $arguments;

	public function __construct($name, array $arguments = array())
	{
		$this->name = $name;
		$this->arguments = $arguments;
	}

	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		$childStack = $this->getChildStack($stack);

		$func = AbstractFunc::create($this->name, $this->arguments);
		return $func->prepare($statement, $section, $childStack, $select, $result);
	}
}