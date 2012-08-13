<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Placeholder\AbstractPlaceholder;

class Placeholder extends AbstractPart
{
	public $name;

	public function __construct($name)
	{
		$this->name = $name;
	}

	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		$prepared = AbstractPlaceholder::create($this->name)->prepare(
			$statement, $section, $stack, $select, $result
		);
		$prepared->setName('%' . $this->name . '%');

		return $prepared;
	}

	public function prepareComparison(
		AbstractPart $lhs, $comparison, Display $statement, $section, array $stack,
		Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		return AbstractPlaceholder::create($this->name)->prepareComparison(
			$lhs, $comparison, $statement, $section, $stack, $select, $result
		);
	}
}