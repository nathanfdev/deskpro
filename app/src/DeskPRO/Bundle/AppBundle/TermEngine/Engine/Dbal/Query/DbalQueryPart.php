<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query;

class DbalQueryPart
{
    /** @var array */
    protected $parameters;

    /** @var array */
    protected $joins;

    /** @var array */
    protected $unique_joins;

    /** @var null|string */
    protected $where;

    public function __construct()
    {
        $this->parameters   = array();
        $this->joins        = array();
        $this->unique_joins = array();
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
            $this->joins[$alias] = array(
                'table' => $join_info['table'],
                'on'    => str_replace(':'.$old_name, ':'.$new_name, $join_info['on']),
                'type'  => $join_info['type'],
            );
        }

        // update unique join conditions
        foreach ($this->unique_joins as $alias => $join_info) {
            $this->unique_joins[$alias] = array(
                'table' => $join_info['table'],
                'on'    => str_replace(':'.$old_name, ':'.$new_name, $join_info['on']),
                'type'  => $join_info['type'],
            );
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
            $this->joins[$alias] = array(
                'table' => $join_info['table'],
                'on'    => str_replace('{'.$old_alias.'}', '{'.$new_alias.'}', $join_info['on']),
                'type'  => $join_info['type'],
            );
        }

        // update unique join conditions
        foreach ($this->unique_joins as $alias => $join_info) {
            $this->unique_joins[$alias] = array(
                'table' => $join_info['table'],
                'on'    => str_replace('{'.$old_alias.'}', '{'.$new_alias.'}', $join_info['on']),
                'type'  => $join_info['type'],
            );

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
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Establish all the query parameters to use in a WHERE/JOIN string at once.
     *
     * @param array $params is the list of parameters in the key => value pair form.
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
     */
    public function addJoin($table, $on_condition, $type = DbalQuery::JOIN_LEFT)
    {
        $this->joins[$table] = array(
            'table' => $table,
            'on'    => $on_condition,
            'type'  => $type,
        );
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
     */
    public function addUniqueJoin($alias, $table, $on_condition, $type = DbalQuery::JOIN_LEFT)
    {
        if ('alias' === $alias) {
            throw new \InvalidArgumentException(
                'cannot use the reserved join alias "alias". Use a different alias name.'
            );
        }

        $this->unique_joins[$alias] = array(
            'table' => $table,
            'on'    => $on_condition,
            'type'  => $type,
        );
    }

    /**
     * You can set a single WHERE string to represent this query part. You can use
     * parameters in the format :param or a unique join alias in the format {alias}
     * or a simple join with the table name.
     *
     * @param $where
     */
    public function setWhereString($where)
    {
        $this->where = (string) $where;
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
