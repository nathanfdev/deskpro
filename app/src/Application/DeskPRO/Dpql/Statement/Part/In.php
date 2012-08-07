<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;

class In extends AbstractPart
{
	public $lhs;
	public $values;
	public $positive;

	public function __construct(AbstractPart $lhs, array $values, $positive = true)
	{
		$this->lhs = $lhs;
		$this->values = $values;
		$this->positive = $positive;
	}

	public function toSql(Display $statement, $section, array $stack)
	{
		$childStack = $this->getChildStack($stack);

		$lhs = $this->lhs->toSql($statement, $section, $childStack);
		$not = ($this->positive ? '' : ' NOT');

		$values = array();
		foreach ($this->values AS $value) {
			$values[] = $value->toSql($statement, $section, $childStack);
		}

		return $lhs . $not . ' IN (' . implode(', ', $values) . ')';
	}
}