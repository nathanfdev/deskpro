<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\App;

class Column extends AbstractPart
{
	public $parts;

	protected static $_tableResolver = array(
		'people' => array('id', 'name')
	);

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
		$printedSql = false;
		$name = false;

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

					switch ($field['type']) {
						case 'datetime':
							$tzOffsetSeconds = App::getCurrentPerson()->getTimezoneOffset() * 3600;
							if ($tzOffsetSeconds) {
								$sql = "($sql + INTERVAL $tzOffsetSeconds SECOND)";
							}
							break;
					}

					$name = $part;
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
						$name = $part;
						break 3; // break $parts loop
					}
				}

				// are we referencing an association?
				if (strtolower($association['fieldName']) == $part) {
					$target = $association['targetEntity'];
					$childRepository = $target::getRepository();

					$childSqlTable = $childRepository->getTableName();
					$joinAlias = "{$sqlTable}_{$association['fieldName']}";

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

					continue 2; // continue $parts loop
				}
			}

			throw new \Exception("Unknown column reference $part");
		}

		if ($partKey !== $lastPartKey) {
			throw new \Exception('Did not get to end of column references');
		}

		if ($sql === false) {
			$assocTable = $repository->getTableName();
			if (isset(self::$_tableResolver[$assocTable])) {
				$resolver = self::$_tableResolver[$assocTable];

				$parent = reset($stack);
				if ($stack) {
					// if we have a parent of any sort, act on the printed value
					$sql = "`$sqlTable`.`$resolver[1]`";
				} else {
					$sql = "`$sqlTable`.`$resolver[0]`";
				}

				$printedSql = "`$sqlTable`.`$resolver[1]`";
			} else {
				throw new \Exception('Did not get SQL from column reference. Just referencing association.');
			}
		}

		return new Prepared($sql, $name, $printedSql);
	}
}