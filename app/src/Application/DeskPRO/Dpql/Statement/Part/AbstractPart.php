<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;

abstract class AbstractPart
{
	//abstract public function validate(Display $statement, $section, array $stack);
	abstract public function toSql(Display $statement, $section, array $stack);

	public function escapeForSql($value)
	{
		return "'" . str_replace(array('\\', "'"), array('\\\\', "\\'"), $value) . "'";
	}

	public function getChildStack(array $stack)
	{
		$childStack = $stack;
		array_unshift($childStack, $this);
		return $childStack;
	}
}