<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;

class Column extends AbstractPart
{
	public $parts;

	public function __construct(array $parts)
	{
		$this->parts = $parts;
	}

	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		$parts = $this->parts;
		$table = array_shift($parts);

		if (strtolower($table) != strtolower($statement->getFrom())) {
			throw new \Exception('Invalid table name in column reference.');
		}

		if (!$parts) {
			throw new \Exception('Missing column/join name in column reference.');
		}

		$sql = false;

		end($parts);
		$lastPartKey = key($parts);

		// represents the repository of what we're joining from
		$repository = $statement->getFromEntityRepository();
		$sqlTable = $repository->getTableName();

		foreach ($parts AS $partKey => $part) {
			// are we referencing a field?
			foreach ($repository->getFieldMappings() AS $key => $field) {
				if (strtolower($key) == $part) {
					$sql = '`' . $sqlTable . '`.`' . $field['columnName'] . '`';
					break 2; // break $parts loop
				}
			}

			foreach ($repository->getAssociationMappings() AS $association) {
				if (empty($association['joinColumns'])) {
					// need to know how to make the join; ignore this
					continue;
				}

				foreach ($association['joinColumns'] AS $joinColumn) {
					// are we referencing a field that is only listed in an association?
					if (strtolower($joinColumn['name']) == $part) {
						$sql = '`' . $sqlTable . '`.`' . $joinColumn['name'] . '`';
						break 3; // break $parts loop
					}
				}

				// are we referencing an association?
				if (strtolower($association['fieldName']) == $part) {
					$target = $association['targetEntity'];
					$childRepository = $target::getRepository();

					$childSqlTable = $childRepository->getTableName();
					$joinAlias = "{$sqlTable}_{$childSqlTable}";

					$joinConditions = array();
					foreach ($association['joinColumns'] AS $joinColumn) {
						$joinConditions[] =
							"`$sqlTable`.`$joinColumn[name]` = "
							. "`$joinAlias`.`$joinColumn[referencedColumnName]`";
					}

					$select->addJoin(
						"$sqlTable|$childSqlTable",
						"LEFT JOIN `$childSqlTable` AS `$joinAlias` ON (" . implode(' AND ', $joinConditions) . ")"
					);

					$repository = $childRepository; // now references come from this table
					$sqlTable = $joinAlias;

					continue 2; // continue parts
				}
			}

			throw new \Exception("Unknown column reference $part");
		}

		if ($partKey !== $lastPartKey) {
			throw new \Exception('Did not get to end of column references');
		}
		if ($sql === false) {
			throw new \Exception('Did not get SQL from column reference. Just referencing association.');
		}

		return $sql;
	}
}