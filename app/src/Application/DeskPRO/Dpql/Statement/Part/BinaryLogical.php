<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql\Parser;

class BinaryLogical extends AbstractPart
{
	public $operator;
	public $lhs;
	public $rhs;

	protected static $_operatorMap = array(
		Parser::T_OP_AND => 'AND',
		Parser::T_OP_OR => 'OR'
	);

	public function __construct($operator, AbstractPart $lhs, AbstractPart $rhs)
	{
		if (!isset(self::$_operatorMap[$operator])) {
			throw new \Exception("Invalid logical operator (token ID: $operator)");
		}

		$this->operator = $operator;
		$this->lhs = $lhs;
		$this->rhs = $rhs;
	}

	public function toSql(Display $statement, $section, array $stack)
	{
		$childStack = $this->getChildStack($stack);

		$lhs = $this->lhs->toSql($statement, $section, $childStack);
		$rhs = $this->rhs->toSql($statement, $section, $childStack);
		$operator = self::$_operatorMap[$this->operator];

		return "($lhs $operator $rhs)";
	}
}