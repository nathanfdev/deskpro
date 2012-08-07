<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;

class Alias extends AbstractPart
{
	public $value;
	public $alias;

	public function __construct(AbstractPart $value, $alias)
	{
		$this->value = $value;
		$this->alias = $alias;
	}

	public function toSql(Display $statement, $section, array $stack)
	{
		$childStack = $this->getChildStack($stack);

		return $this->value->toSql($statement, $section, $childStack)
			. ' AS ' . $this->escapeForSql($this->alias);
	}
}