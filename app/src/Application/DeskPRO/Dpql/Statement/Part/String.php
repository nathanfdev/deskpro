<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;

class String extends AbstractPart
{
	public $string;

	public function __construct($string)
	{
		$this->string = $string;
	}

	public function toSql(Display $statement, $section, array $stack)
	{
		return $this->escapeForSql($this->string);
	}
}