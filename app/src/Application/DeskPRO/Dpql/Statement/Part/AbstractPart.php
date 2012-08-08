<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;

abstract class AbstractPart
{
	abstract public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	);

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