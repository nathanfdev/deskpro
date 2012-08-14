<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Parser;
use Application\DeskPRO\Dpql\Exception;

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
			throw new Exception("Invalid math operator (token ID: $operator)");
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

	public function toDpql(Display $statement, $section, array $stack)
	{
		return $this->lhs->toDpql($statement, $section, $stack)
			. ' ' . self::$_operatorMap[$this->operator] . ' '
			. $this->rhs->toDpql($statement, $section, $stack);
	}
}