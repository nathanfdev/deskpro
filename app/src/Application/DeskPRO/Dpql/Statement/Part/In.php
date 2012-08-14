<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;

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

	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		$childStack = $this->getChildStack($stack);

		$lhs = $this->lhs->prepare($statement, $section, $childStack, $select, $result);
		$not = ($this->positive ? '' : ' NOT');

		$valuesSql = array();
		$valuesName = array();
		foreach ($this->values AS $value) {
			$prepped = $value->prepare($statement, $section, $childStack, $select, $result);
			$valuesSql[] = $prepped->sql();
			$valuesName[] = $prepped->name();
		}

		$sql = "{$lhs->sql()}$not IN (" . implode(', ', $valuesSql) . ')';
		return new Prepared($sql, "{$lhs->name()}$not IN (" . implode(', ', $valuesName) . ')');
	}

	public function toDpql(Display $statement, $section, array $stack)
	{
		$values = array();
		foreach ($this->values AS $value) {
			$values[] = $value->toDpql($statement, $section, $stack);
		}

		$not = ($this->positive ? '' : ' NOT');

		return $this->name . $not . ' IN (' . implode(', ', $values) . ')';
	}
}