<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;

class Like extends AbstractPart
{
	public $lhs;
	public $rhs;
	public $positive;

	public function __construct(AbstractPart $lhs, AbstractPart $rhs, $positive = true)
	{
		$this->lhs = $lhs;
		$this->rhs = $rhs;
		$this->positive = $positive;
	}

	public function toSql(Display $statement, $section, array $stack)
	{
		$childStack = $this->getChildStack($stack);

		$lhs = $this->lhs->toSql($statement, $section, $childStack);
		$rhs = $this->rhs->toSql($statement, $section, $childStack);
		$not = ($this->positive ? '' : ' NOT');

		return "$lhs$not LIKE $rhs";
	}
}