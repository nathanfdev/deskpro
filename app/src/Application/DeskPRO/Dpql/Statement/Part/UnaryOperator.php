<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Parser;

class UnaryOperator extends AbstractPart
{
	public $operator;
	public $value;

	protected static $_operatorMap = array(
		Parser::T_OP_BANG => '!',
		Parser::T_OP_U_MINUS => '-',
		Parser::T_OP_MINUS => '-',
		Parser::T_OP_NOT => 'NOT ', // space after is important
	);

	public function __construct($operator, AbstractPart $value)
	{
		$this->operator = $operator;
		$this->value = $value;
	}

	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		$childStack = $this->getChildStack($stack);

		$value = $this->value->prepare($statement, $section, $childStack, $select, $result);
		$operator = self::$_operatorMap[$this->operator];

		return new Prepared("($operator{$value->sql()}", "$operator{$value->name()}");
	}
}