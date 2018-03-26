<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query;

/**
 * Class DbalQueryBuilder.
 */
class DbalQueryBuilder
{
    /**
     * @var DbalQuery
     */
    private $query;

    /**
     * Constructor.
     *
     * @param DbalQuery $query
     */
    public function __construct(DbalQuery $query)
    {
        $this->query = $query;
    }

    /**
     * @return DbalQuery
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * ONLY the main compiler should use this. It will replace the existing WHERE string
     * in the query with a new one.
     *
     * @param $new_where_string
     */
    public function setWhereString($new_where_string)
    {
        $this->query->setWherePart($new_where_string);
    }

    /**
     * Set a parameter, but the $name_prefix is just a prefix. The actual parameter
     * name will be returned to you.
     *
     * @param $name_prefix
     * @param $value
     *
     * @return string the parameter name
     */
    public function addParameter($name_prefix, $value)
    {
        return $this->query->addParameter($name_prefix, $value);
    }

    /**
     * This will do nothing if the table is already joined, else it will join the
     * table with the given ON.
     *
     * @param $table_name
     * @param string $on
     */
    public function addJoin($table_name, $on)
    {
        $this->query->addJoin($table_name, $on);
    }

    /**
     * You can use {alias} in the $on param as a placeholder for the real join alias.
     *
     * It returns the real join alias so you can reference it.
     *
     * @param $table_name
     * @param string $on
     * @param string $type
     *
     * @return string the join alias
     */
    public function addUniqueJoin($table_name, $on, $type = 'LEFT')
    {
        return $this->query->addUniqueJoin($table_name, $on, $type);
    }

    /**
     * What table are we selecting from?
     *
     * @param $table
     * @param $alias
     */
    public function setFrom($table, $alias)
    {
        $this->query->setFrom($table, $alias);
    }

    /**
     * Take a DbalQueryPart, and add it to the query.
     *
     * @param DbalQueryPart $query_part
     */
    public function writeQueryPart(DbalQueryPart $query_part)
    {
        // this will create the real "new" param names, and replace the DbalQueryPart correctly
        foreach ($query_part->getParameters() as $param_name => $param_val) {
            $new_param_name = $this->addParameter($param_name, $param_val);
            $query_part->renameParam($param_name, $new_param_name);
        }

        // keep track of renames, and update all joins with new join names given by DbalQuery
        // NOTE: if a unique join references a join that is added to the QueryPart at a later
        //       time, there will be an issue with the query
        $join_renames = [];
        foreach ($query_part->getUniqueJoins() as $alias => $join_info) {
            // replace the proposed alias with "alias", because query will replace it with the real alias
            $on = $join_info['on'];
            $on = str_replace('{'.$alias.'}', '{alias}', $on);
            // if any joins exist, replace the old alias with the new in the existing join ONs
            // this allows multiple unique joins to reference each other
            foreach ($join_renames as $old => $new) {
                $on = str_replace('{'.$old.'}', $new, $on);
                $on = str_replace('{'.$new.'}', $new, $on);
            }
            $join_alias           = $this->addUniqueJoin($join_info['table'], $on, $join_info['type']);
            $join_renames[$alias] = $join_alias;
            // everything still in the $query_part needs to be renamed to the real alias in the query
            $query_part->renameJoinAlias($alias, $join_alias);
        }

        foreach ($query_part->getJoins() as $join_alias => $join_info) {
            $this->addJoin($join_info['table'], $join_info['on']);
        }

        $where = $query_part->getWhereString();
        if ($where) {
            // now we must "inject" the proper join names into the WHERE clause
            foreach ($join_renames as $old => $new) {
                $where = str_replace('{'.$old.'}', $new, $where);
                $where = str_replace('{'.$new.'}', $new, $where);
            }
            $this->setWhereString($where);
        }
    }
}
