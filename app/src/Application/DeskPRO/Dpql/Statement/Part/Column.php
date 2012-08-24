<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Dpql
 */

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\App;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Application\DeskPRO\Dpql\Exception;
use Application\DeskPRO\Dpql\Renderer\Values\AbstractValues;
use Application\DeskPRO\Dpql\Renderer\AbstractRenderer;
use Application\DeskPRO\Dpql\Func\Link;

/**
 * Represents a reference to a column or association.
 */
class Column extends AbstractPart
{
	/**
	 * List of parts in the reference
	 *
	 * @var array
	 */
	public $parts;

	/**
	 * This is used when resolving direct association references to specific columns.
	 * Maps a table name to 2 values:
	 *  - 0: the unique ID field (usually a number)
	 *  - 1: the printable field (name, subject, etc)
	 *
	 * @var array
	 */
	protected static $_tableResolver = array(
		'agent_teams' => array('id', 'name'),
		'departments' => array('id', 'title'),
		'languages' => array('id', 'name'),
		'organizations' => array('id', 'name'),
		'people' => array('id', 'name', 'person'),
		'tickets' => array('id', 'subject', 'ticket'),
		'ticket_categories' => array('id', 'title'),
		'ticket_priorities' => array('id', 'title')
	);

	/**
	 * @param array $parts
	 */
	public function __construct(array $parts)
	{
		$this->parts = $parts;
	}

	/**
	 * Prepares a part for use, including validating that the usage is valid.
	 *
	 * @param \Application\DeskPRO\Dpql\Statement\Display $statement
	 * @param string $section Name of the section usage is in (select, where, split, group, order)
	 * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[] $stack Parent parts
	 * @param \Application\DeskPRO\Dpql\SqlSelect $select Select being built up
	 * @param \Application\DeskPRO\Dpql\ResultHandler $result
	 *
	 * @throws \Application\DeskPRO\Dpql\Exception
	 *
	 * @return \Application\DeskPRO\Dpql\Statement\Part\Prepared|bool Prepared results or false if there's no output
	 */
	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		$parts = $this->parts;
		$table = array_shift($parts);

		if (strtolower($table) != strtolower($statement->getFrom())) {
			throw new Exception("Invalid table name in column reference (received $table, expected {$statement->getFrom()}).");
		}

		if (!$parts) {
			throw new Exception('Missing column/join name in column reference.');
		}

		$sql = false;
		$printedSql = false;
		$name = false;
		$renderer = null;

		end($parts);
		$lastPartKey = key($parts);

		// represents the repository of what we're joining from
		$repository = $statement->getFromEntityRepository();
		$sqlTable = $repository->getTableName();

		$partsSoFar = array($table);

		foreach ($parts AS $partKey => $part) {
			$partsSoFar[] = $part;
			$partsString = implode('.', $partsSoFar);

			// are we referencing a field?
			foreach ($repository->getFieldMappings() AS $key => $field) {
				if (strtolower($key) == $part) {
					if (isset($field['dpqlAccess']) && !$field['dpqlAccess']) {
						throw new Exception("$partsString cannot be accessed via DPQL.");
					}

					$sql = '`' . $sqlTable . '`.`' . $field['columnName'] . '`';

					switch ($field['type']) {
						case 'datetime':
							$tzOffsetSeconds = $statement->getTimezoneOffsetForFunction($stack);
							if ($tzOffsetSeconds) {
								$sql = "($sql + INTERVAL $tzOffsetSeconds SECOND)";
							}

							$renderer = 'datetime';
							break;

						case 'integer':
						case 'smallint':
						case 'bigint':
						case 'decimal':
						case 'float':
							$renderer = 'number';
							break;

						case 'date':
							$renderer = 'date';
							break;

						case 'time':
							$renderer = 'time';
							break;

						case 'boolean':
							$renderer = 'boolean';
							break;

						case 'string':
						case 'text':
							$renderer = 'string';
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
			}

			foreach ($repository->getAssociationMappings() AS $association) {
				// are we referencing an association?
				if (strtolower($association['fieldName']) == $part) {
					$target = $association['targetEntity'];
					$childRepository = $target::getRepository();

					if ((isset($association['dpqlAccess']) && !$association['dpqlAccess'])
						|| !($childRepository instanceof \Application\DeskPRO\EntityRepository\AbstractEntityRepository)
						|| $association['type'] == ClassMetadataInfo::MANY_TO_MANY
					) {
						throw new Exception("$partsString cannot be accessed via DPQL.");
					}

					$childSqlTable = $childRepository->getTableName();
					$joinAlias = "{$sqlTable}_{$association['fieldName']}";

					if (!empty($association['joinColumns'])) {
						// join can be resolved directly
						$joinColumns = $association['joinColumns'];
						$sourceTable = $sqlTable;
						$joinTable = $joinAlias;
					} else {
						$childAssociations = $childRepository->getAssociationMappings();
						if (!empty($childAssociations[$association['mappedBy']]['joinColumns'])) {
							// join details are on the other table
							$joinColumns = $childAssociations[$association['mappedBy']]['joinColumns'];
							$sourceTable = $joinAlias;
							$joinTable = $sqlTable;
						} else {
							$joinColumns = array();
						}
					}

					if (!$joinColumns) {
						throw new Exception("$partsString cannot be accessed via DPQL.");
					}

					$joinConditions = array();
					foreach ($joinColumns AS $joinColumn) {
						$joinConditions[] =
							"`$sourceTable`.`$joinColumn[name]` = "
							. "`$joinTable`.`$joinColumn[referencedColumnName]`";
					}

					$select->addJoin(
						"$joinAlias",
						"LEFT JOIN `$childSqlTable` AS `$joinAlias` ON (" . implode(' AND ', $joinConditions) . ")"
					);

					$repository = $childRepository; // now references come from this table
					$sqlTable = $joinAlias;

					continue 2; // continue $parts loop
				}
			}

			throw new Exception("Unknown column reference $partsString");
		}

		if ($partKey !== $lastPartKey) {
			throw new Exception('Did not get to end of column references');
		}

		if ($sql === false) {
			$assocTable = $repository->getTableName();
			$name = $part;
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

				if (isset($resolver[2])) {
					if ($section == 'split') {
						$argSelect = array($statement->getSplitSql()->addSelectField("`$sqlTable`.`$resolver[0]`"));
					} else {
						$argSelect = array($select->addSelectField("`$sqlTable`.`$resolver[0]`"));
					}

					$renderer = function(AbstractValues $valueRenderer, $value, array $row, AbstractRenderer $renderer)
						use ($resolver, $argSelect)
					{
						return Link::formatLink($value, $resolver[2], $argSelect, $row, $valueRenderer, $renderer);
					};
				}
			} else {
				throw new Exception("$partsString cannot be referenced directly. Please reference a specific column.");
			}
		}

		return new Prepared($sql, $this->_prettifyColumnName($name), $printedSql, $renderer);
	}

	/**
	 * Renders a part back to DPQL.
	 *
	 * @param \Application\DeskPRO\Dpql\Statement\Display $statement
	 * @param string $section
	 * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[] $stack
	 *
	 * @return string
	 */
	public function toDpql(Display $statement, $section, array $stack)
	{
		return implode('.', $this->parts);
	}

	/**
	 * Turns a column reference (such as ticket_id) into a nicer looking,
	 * printable version (Ticket ID).
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	protected function _prettifyColumnName($name)
	{
		$name = str_replace('_', ' ', $name);
		$name = ucwords($name);
		$name = str_replace('Id', 'ID', $name);

		return $name;
	}
}