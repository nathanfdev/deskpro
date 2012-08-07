<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql\Parser;

class BinaryMath extends AbstractPart
{
	public $operator;
	public $lhs;
	public $rhs;

	protected static $_operatorMap = array(
		Parser::T_OP_PLUS => '+',
		Parser::T_OP_MINUS => '-',
		Parser::T_OP_MULTIPLY => '*',
		Parser::T_OP_DIVIDE => '/'
	);

	public function __construct($operator, AbstractPart $lhs, AbstractPart $rhs)
	{
		if (!isset(self::$_operatorMap[$operator])) {
			throw new \Exception("Invalid math operator (token ID: $operator)");
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