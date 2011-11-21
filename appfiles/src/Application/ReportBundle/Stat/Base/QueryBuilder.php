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
	public function delete()
	{
		return $this;
	}

	/**
	 * Override the base, Not allowed to update when querying for reporting
	 *
	 * @return QueryBuilder This QueryBuilder instance
	 */
	public function update()
	{
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