<?php

namespace Application\ReportBundle\Stat\Base;

/**
 * Basic Query builder class.
 *
 * Could probably use the Doctrine\DBAL\Query\QueryBuilder class with this
 * aswell
 */
class QueryBuilder extends \Doctrine\DBAL\Query\QueryBuilder
{
	/**
	 * List of table aliases
	 *
	 * @var array
	 */
	protected $table_aliases = array();

	/**
	 * Override the base, Not allowed to delete when querying for reporting
	 *
	 * @return QueryBuilder This QueryBuilder instance
	 */
	public function delete($delete = null, $alias = null)
	{
		throw new \Exception("Delete operation not permitted");
		return $this;
	}

	/**
	 * Override the base, Not allowed to update when querying for reporting
	 *
	 * @return QueryBuilder This QueryBuilder instance
	 */
	public function update($update = null, $alias = null)
	{
		throw new \Exception("Update operation not permitted");
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function from($from, $alias)
	{
		$this->addTableAlias($from, $alias);

		return parent::from($from, $alias);
	}

	/**
	 * Adds a join by its full SQL, example
	 * LEFT JOIN users AS u ON (t.user_id = u.id)
	 *
	 * Avoid using this method unless absolutly required (ie you only have the
	 * join statement in its full form), use one of the
	 * more explict join methods such as leftJoin, innerJoin, and rightJoin.
	 */
	public function addJoin($fromAlias, $join)
	{
		$join = strtolower($join);

		list($joinType, $rest) = explode('join', $join);
		list($table, $condition) = explode('on', $rest);

		// TODO: AS maynot be used, should always work for ' '
		list($joinTable, $joinAlias) = explode('as', $table);

		$joinType  = trim($joinType);
		$joinTable = trim($joinTable);
		$joinAlias = trim($joinAlias);
		$condition = trim($condition);

		switch ($joinType) {
			case 'left':
				$this->leftJoin($fromAlias, $joinTable, $joinAlias, $condition);
				break;
			case 'right':
				$this->rightJoin($fromAlias, $joinTable, $joinAlias, $condition);
				break;
			case 'inner':
				$this->innerJoin($fromAlias, $joinTable, $joinAlias, $condition);
				break;
			default:
				throw new \Exception("Unsupported join type");
		}
	}

	/**
	 * @inheritdoc
	 */
	public function join($fromAlias, $join, $alias, $condition = null)
	{
		$this->addTableAlias($join, $alias);

		return parent::join($fromAlias, $join, $alias, $condition);
	}

	/**
	 * @inheritdoc
	 */
	public function innerJoin($fromAlias, $join, $alias, $condition = null)
	{
		$this->addTableAlias($join, $alias);

		return parent::innerJoin($fromAlias, $join, $alias, $condition);
	}

	/**
	 * @inheritdoc
	 */
	public function leftJoin($fromAlias, $join, $alias, $condition = null)
	{
		$this->addTableAlias($join, $alias);

		return parent::leftJoin($fromAlias, $join, $alias, $condition);
	}

	/**
	 * @inheritdoc
	 */
	public function rightJoin($fromAlias, $join, $alias, $condition = null)
	{
		$this->addTableAlias($join, $alias);

		return parent::rightJoin($fromAlias, $join, $alias, $condition);
	}

	/**
	 * Get the query select component
	 *
	 * @param bool $split True to split the selected fields into an array
	 * @return mixed The select component
	 */
	public function getSelect($split = true)
	{
		$selects = $this->getQueryPart('select');
		if (count($selects)) {
			if (true === $split) {
				$fields = array();
				foreach ($selects as $select) {
					foreach (explode(',', $select) as $field) {
						$fields[] = trim($field);
					}
				}
				return $fields;
			}
			else {
				return join(', ', $selects);
			}
		}
	}

	/**
	 * Checks if a field is being selected
	 *
	 * @return bool
	 */
	public function isFieldSelected($field)
	{
		$selected_fields = $this->getSelect();

		return isset($selected_fields[$field]) ? true : false;
	}

	/**
	 * Get an alias for a table
	 *
	 * @param string $table The table name
	 * @return string The table alias, or the $table name if no alias is found
	 */
	public function getTableAlias($table)
	{
		return isset($this->table_aliases[$table]) ? $this->table_aliases[$table] : $table;
	}

	/**
	 * Adds an internal reference for a table alias
	 *
	 * @param string $table The table name
	 * @param string $alias The alias name
	 */
	protected function addTableAlias($table, $alias)
	{
		$this->table_aliases[$table] = $alias;
	}
}