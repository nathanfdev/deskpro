<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters\Terms;

use Orb\Util\Arrays;
use Orb\Util\Util;

class FilterQuery
{
	/**
	 * @var array
	 */
	private $joins = array();

	/**
	 * @var array
	 */
	private $wheres_and = array();

	/**
	 * @var array
	 */
	private $wheres_or  = array();

	/**
	 * @var array
	 */
	private $params = array();

	/**
	 * Array of string replacements to do on the resulting query parts
	 *
	 * @var array
	 */
	private $var_renamed = array();

	/**
	 * Appends a unique string to the end of a name to make it unique.
	 * Eg: "name_param" becomes "name_param_dp_a"
	 *
	 * @param string $base
	 * @param string $type
	 * @return string
	 */
	private function getUniqueName($base, $type)
	{
		static $count = 0;
		$count++;

		return "__dp{$type}_{$base}_" . Util::baseEncode($count, 'letters') . '__';
	}

	/**
	 * @param string $fromAlias
	 * @param string $join
	 * @param string $alias
	 * @param null   $condition
	 */
	public function addJoin($fromAlias, $join, $alias, $condition = null)
	{
		$m = null;
		$input_alias = $alias;
		$alias = null;
		if (preg_match('#unique:([0-9a-zA-Z_]+])#', $alias, $m)) {
			$alias = $this->getUniqueName($m[1], 'join');
			$this->var_renamed["{table.{$input_alias}}"] = $alias;
		}

		$this->joins[$input_alias] = array(
			'fromAlias'   => $fromAlias,
			'join'        => $join,
			'input_alias' => $input_alias,
			'alias'       => $alias,
			'condition'   => $condition
		);
	}


	/**
	 * @param string $where
	 */
	public function andWhere($where)
	{
		$this->wheres_and[] = $where;
	}


	/**
	 * Generates the proper 'where in(?,?,?)' code
	 *
	 * @param $field_name
	 * @param array $params
	 * @param bool $not
	 */
	public function andWhereIn($field_name, array $params, $not = false)
	{
		if (!$params) {
			if ($not) $this->andWhere("1");
			else $this->andWhere("0");
			return;
		}

		$names = array();
		foreach (array_values($params) as $k => $p) {
			$names[] = "{param.in$k}";
			$this->setParameter("in$k", $p);
		}

		$not_str = $not ? "NOT " : "";
		$this->andWhere("$field_name {$not_str}IN (" . implode(',', $names) . ")");
	}


	/**
	 * Generates the proper 'where in(?,?,?)' code
	 *
	 * @param $field_name
	 * @param array $params
	 * @param bool $not
	 */
	public function orWhereIn($field_name, array $params, $not = false)
	{
		if (!$params) {
			if ($not) $this->andWhere("1");
			else $this->andWhere("0");
			return;
		}

		$names = array();
		foreach (array_values($params) as $k => $p) {
			$names[] = "{param.in$k}";
			$this->setParameter("in$k", $p);
		}

		$not_str = $not ? "NOT " : "";
		$this->orWhere("$field_name {$not_str}IN (" . implode(',', $names) . ")");
	}


	/**
	 * @param string $where
	 */
	public function orWhere($where)
	{
		$this->wheres_or[] = $where;
	}


	/**
	 * @param string $name
	 * @param mixed $value
	 * @param null  $type
	 */
	public function setParameter($name, $value, $type = null, $rename = true)
	{
		$input_name = $name;

		if ($rename) {
			$name = $this->getUniqueName($input_name, 'param');
			$this->var_renamed["{param.{$input_name}}"] = $name;
		}

		$this->params[$name] = array(
			'name'       => $name,
			'input_name' => $input_name,
			'value'      => $value,
			'type'       => $type
		);
	}


	/**
	 * @return array
	 */
	public function getQueryParts()
	{
		$joins  = array_values($this->joins);
		$params = array_values($this->params);

		$where_and = null;
		if ($this->wheres_and) {
			$where_and = '(' . implode(') AND (', $this->wheres_and) . ')';
		}

		$where_or = null;
		if ($this->wheres_or) {
			$where_or = '(' . implode(') OR (', $this->wheres_or) . ')';
		}

		foreach ($joins as &$j) {
			if ($j['condition']) {
				$j['condition'] = str_replace(array_keys($this->var_renamed), array_values($this->var_renamed), $j['condition']);
			}
		}

		if ($where_or || $where_and) {
			$where = array($where_and, $where_or);
			$where = Arrays::removeFalsey($where);
			$where = '(' . implode(') AND (', $where) . ')';
			$where = str_replace(array_keys($this->var_renamed), array_values($this->var_renamed), $where);
		} else {
			$where = '1';
		}

		return array(
			'joins'    => $joins,
			'params'   => $params,
			'where'    => $where,
		);
	}
}