<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;

class Parentheses extends AbstractPart
{
	public $expression;

	public function __construct($expression)
	{
		$this->expression = $expression;
	}

	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		$prepared = $this->expression->prepare($statement, $section, $stack, $select, $result);
		$prepared->setName('(' . $prepared->name() . ')');
		return $prepared;
	}

	public function toDpql(Display $statement, $section, array $stack)
	{
		return '(' . $this->expression->toDpql($statement, $section, $stack) . ')';
	}
}