<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;

class Number extends AbstractPart
{
	public $number;

	public function __construct($number)
	{
		$this->number = $number;
	}

	public function toSql(Display $statement, $section, array $stack)
	{
		return $this->number + 0;
	}
}