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

		$values = array();
		foreach ($this->values AS $value) {
			$values[] = $value->prepare($statement, $section, $childStack, $select, $result);
		}

		return $lhs . $not . ' IN (' . implode(', ', $values) . ')';
	}
}