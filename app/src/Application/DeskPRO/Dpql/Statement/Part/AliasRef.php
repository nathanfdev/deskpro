<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;

class AliasRef extends AbstractPart
{
	public $alias;

	public function __construct($alias)
	{
		$this->alias = $alias;
	}

	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		$fieldId = $statement->getSqlSelectFieldId($this->alias);
		$sql = ($fieldId !== false ? $select->getSelectField($fieldId) : 'NULL');

		return new Prepared($sql, $this->alias);
	}

	public function toDpql(Display $statement, $section, array $stack)
	{
		return '@' . $statement->quoteDpqlString($this->alias);
	}
}