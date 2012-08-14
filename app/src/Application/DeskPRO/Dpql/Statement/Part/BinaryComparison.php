<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Parser;
use Application\DeskPRO\Dpql\Exception;

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

	protected static $_operatorOrderFlipped = array(
		'=' => '=',
		'<>' => '<>',
		'>' => '<',
		'>=' => '<=',
		'<' => '>',
		'<=' => '>='
	);

	public function __construct($operator, AbstractPart $lhs, AbstractPart $rhs)
	{
		if (!isset(self::$_operatorMap[$operator])) {
			throw new Exception("Invalid comparison operator (token ID: $operator)");
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

		$lhs = $this->lhs;
		$rhs = $this->rhs;
		$operator = self::$_operatorMap[$this->operator];

		if ($lhs instanceof Placeholder) {
			// flip as placeholder comparison expects placeholder on RHS
			$temp = $lhs;
			$lhs = $rhs;
			$rhs = $temp;
			$operator = self::$_operatorOrderFlipped[$operator];
		}

		if ($rhs instanceof Placeholder) {
			$prepared = $rhs->prepareComparison(
				$lhs, $operator, $statement, $section, $childStack, $select, $result
			);
			if ($prepared) {
				return $prepared;
			}
		}

		$lhsRes = $lhs->prepare($statement, $section, $childStack, $select, $result);
		$rhsRes = $rhs->prepare($statement, $section, $childStack, $select, $result);

		$sql = "({$lhsRes->sql()} $operator {$rhsRes->sql()})";
		return new Prepared($sql, "{$lhsRes->name()} $operator {$rhsRes->name()}");
	 }

	public function toDpql(Display $statement, $section, array $stack)
	{
		return $this->lhs->toDpql($statement, $section, $stack)
			. ' ' . self::$_operatorMap[$this->operator] . ' '
			. $this->rhs->toDpql($statement, $section, $stack);
	}
}