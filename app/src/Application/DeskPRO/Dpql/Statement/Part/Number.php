<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;

class Number extends AbstractPart
{
	public $number;

	public function __construct($number)
	{
		$this->number = $number;
	}

	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		$value = strval($this->number + 0);
		return new Prepared($value, $value);
	}
}