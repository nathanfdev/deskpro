<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Parser;

class BinaryComparison extends AbstractPart
{
	public $operator;
	public $lhs;
	public $rhs;

	protected static $_operatorMap = array(
		Parser::T_OP_EQ => '=',
		Parser::T_OP_NE => '<>',
		Parser::T_OP_GT => '>',
		Parser::T_OP_GTEQ => '>=',
		Parser::T_OP_LT => '<',
		Parser::T_OP_LTEQ => '<='
	);

	public function __construct($operator, AbstractPart $lhs, AbstractPart $rhs)
	{
		if (!isset(self::$_operatorMap[$operator])) {
			throw new \Exception("Invalid comparison operator (token ID: $operator)");
		}

		$this->operator = $operator;
		$this->lhs = $lhs;
		$this->rhs = $rhs;
	}

	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		$childStack = $this->getChildStack($stack);

		$lhs = $this->lhs->prepare($statement, $section, $childStack, $select, $result);
		$rhs = $this->rhs->prepare($statement, $section, $childStack, $select, $result);
		$operator = self::$_operatorMap[$this->operator];

		$sql = "({$lhs->sql()} $operator {$rhs->sql()})";
		return new Prepared($sql, "{$lhs->name()} $operator {$rhs->name()}");
	 }
}