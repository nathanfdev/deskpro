<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query;

/**
 * Class DbalQueryPart.
 */
class DbalQueryPart
{
    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var array
     */
    protected $joins;

    /**
     * @var array
     */
    protected $unique_joins;

    /**
     * @var null|string
     */
    protected $where;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->parameters   = [];
        $this->joins        = [];
        $this->unique_joins = [];
        $this->where        = null;
    }

    /**
     * Renames a parameter (all join ON strings and there WHERE string update).
     *
     * @param $old_name
     * @param $new_name
     */
    public function renameParam($old_name, $new_name)
    {
        // rename param
        $val                         = $this->parameters[$old_name];
        $this->parameters[$new_name] = $val;
        unset($this->parameters[$old_name]);

        // update where string
        $this->where = str_replace(':'.$old_name, ':'.$new_name, $this->where);

        // update join conditions
        foreach ($this->joins as $alias => $join_info) {
            $this->joins[$alias] = [
                'table' => $join_info['table'],
                'on'    => str_replace(':'.$old_name, ':'.$new_name, $join_info['on']),
                'type'  => $join_info['type'],
            ];
        }

        // update unique join conditions
        foreach ($this->unique_joins as $alias => $join_info) {
            $this->unique_joins[$alias] = [
                'table' => $join_info['table'],
                'on'    => str_replace(':'.$old_name, ':'.$new_name, $join_info['on']),
                'type'  => $join_info['type'],
            ];
        }
    }

    /**
     * Rename a join alias (all join ON strings and there WHERE string update).
     *
     * @param $old_alias
     * @param $new_alias
     */
    public function renameJoinAlias($old_alias, $new_alias)
    {
        // update join conditions
        foreach ($this->joins as $alias => $join_info) {
            $this->joins[$alias] = [
                'table' => $join_info['table'],
                'on'    => str_replace('{'.$old_alias.'}', '{'.$new_alias.'}', $join_info['on']),
                'type'  => $join_info['type'],
            ];
        }

        // update unique join conditions
        foreach ($this->unique_joins as $alias => $join_info) {
            $this->unique_joins[$alias] = [
                'table' => $join_info['table'],
                'on'    => str_replace('{'.$old_alias.'}', '{'.$new_alias.'}', $join_info['on']),
                'type'  => $join_info['type'],
            ];

            if ($alias == $old_alias) {
                $this->unique_joins[$new_alias] = $this->unique_joins[$old_alias];
                unset($this->unique_joins[$old_alias]);
            }
        }

        $this->where = str_replace('{'.$old_alias.'}', '{'.$new_alias.'}', $this->where);
    }

    /**
     * Establish a parameter that can be used in WHERE/JOIN strings.
     *
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * Establish all the query parameters to use in a WHERE/JOIN string at once.
     *
     * @param array $params is the list of parameters in the key => value pair form
     */
    public function setParameters(array $params)
    {
        $this->parameters = $params;
    }

    /**
     * A simple join uses the table name as the alias. You can use the table name in
     * WHERE strings or other JOIN strings.
     *
     * @param $table
     * @param $on_condition
     * @param string $type
     *
     * @return $this
     */
    public function addJoin($table, $on_condition, $type = DbalQuery::JOIN_LEFT)
    {
        $this->joins[$table] = [
            'table' => $table,
            'on'    => $on_condition,
            'type'  => $type,
        ];

        return $this;
    }

    /**
     * A unique join lets you specify the alias of the query. You can use the alias in the
     * format {alias} (replace "alias" with the $alias you pass in here) in WHERE and JOIN
     * strings.
     *
     * @param $alias
     * @param $table
     * @param $on_condition
     * @param string $type
     *
     * @return $this
     */
    public function addUniqueJoin($alias, $table, $on_condition, $type = DbalQuery::JOIN_LEFT)
    {
        if ('alias' === $alias) {
            throw new \InvalidArgumentException(
                'cannot use the reserved join alias "alias". Use a different alias name.'
            );
        }

        $this->unique_joins[$alias] = [
            'table' => $table,
            'on'    => $on_condition,
            'type'  => $type,
        ];

        return $this;
    }

    /**
     * You can set a single WHERE string to represent this query part. You can use
     * parameters in the format :param or a unique join alias in the format {alias}
     * or a simple join with the table name.
     *
     * @param $where
     *
     * @return $this
     */
    public function setWhereString($where)
    {
        $this->where = (string) $where;

        return $this;
    }

    /**
     * Get all current parameters with the correct name (in the case of renames it
     * is using the new name, the old name does not exist anymore).
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Gets the internal representation of the joins (an array with the table name
     * as the key) in case you want to introspect.
     *
     * @return array
     */
    public function getJoins()
    {
        return $this->joins;
    }

    /**
     * Get the internal representation of the unique joins (an array with the alias as
     * the key) in case you want to introspect.
     *
     * @return array
     */
    public function getUniqueJoins()
    {
        return $this->unique_joins;
    }

    /**
     * Get the current WHERE string.
     *
     * @return null|string
     */
    public function getWhereString()
    {
        return $this->where;
    }
}
