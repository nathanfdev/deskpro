<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;

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

	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		$childStack = $this->getChildStack($stack);

		$lhs = $this->lhs->prepare($statement, $section, $childStack, $select, $result);
		$rhs = $this->rhs->prepare($statement, $section, $childStack, $select, $result);
		$not = ($this->positive ? '' : ' NOT');

		$sql = "{$lhs->sql()}$not LIKE {$rhs->sql()}";
		return new Prepared($sql, "{$lhs->name()}$not LIKE {$rhs->name()}");
	}

	public function toDpql(Display $statement, $section, array $stack)
	{
		$not = ($this->positive ? '' : ' NOT');

		return $this->lhs->toDpql($statement, $section, $stack)
			. $not . ' LIKE '
			. $this->rhs->toDpql($statement, $section, $stack);
	}
}