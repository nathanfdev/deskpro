<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Exception;

class Alias extends AbstractPart
{
	public $value;
	public $alias;

	public function __construct(AbstractPart $value, $alias)
	{
		$this->value = $value;
		$this->alias = $alias;
	}

	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		throw new Exception('Alias prepare() cannot not be called');
	}

	public function toDpql(Display $statement, $section, array $stack)
	{
		return $this->value->toDpql($statement, $section, $stack)
			. ' AS ' . $statement->quoteDpqlString($this->alias);
	}
}