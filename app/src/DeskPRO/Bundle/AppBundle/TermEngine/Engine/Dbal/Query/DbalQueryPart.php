<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query;

class DbalQueryPart
{
    protected $parameters;
    protected $joins;
    protected $unique_joins;
    protected $where;

    public function __construct()
    {
        $this->parameters = array();
        $this->joins = array();
        $this->unique_joins = array();
        $this->where = null;
    }

    public function renameParam($old_name, $new_name)
    {
        // rename param
        $val = $this->parameters[$old_name];
        $this->parameters[$new_name] = $val;
        unset($this->parameters[$old_name]);

        // update where string
        $this->where = str_replace(':' . $old_name, ':' . $new_name, $this->where);

        // update join conditions
        foreach ($this->joins as $alias => $join_info) {
            $this->joins[$alias] = array(
                'table' => $join_info['table'],
                'on' => str_replace(':' . $old_name, ':' . $new_name, $join_info['on']),
                'type' => $join_info['type']
            );
        }

        // update unique join conditions
        foreach ($this->unique_joins as $alias => $join_info) {
            $this->unique_joins[$alias] = array(
                'table' => $join_info['table'],
                'on' => str_replace(':' . $old_name, ':' . $new_name, $join_info['on']),
                'type' => $join_info['type']
            );
        }
    }

    public function renameJoinAlias($old_alias, $new_alias)
    {
        // update join conditions
        foreach ($this->joins as $alias => $join_info) {
            $this->joins[$alias] = array(
                'table' => $join_info['table'],
                'on' => str_replace('{' . $old_alias . '}', '{' . $new_alias . '}', $join_info['on']),
                'type' => $join_info['type']
            );
        }

        // update unique join conditions
        foreach ($this->unique_joins as $alias => $join_info) {
            $this->unique_joins[$alias] = array(
                'table' => $join_info['table'],
                'on' => str_replace('{' . $old_alias . '}', '{' . $new_alias . '}', $join_info['on']),
                'type' => $join_info['type']
            );

            if ($alias == $old_alias) {
                $this->unique_joins[$new_alias] = $this->unique_joins[$old_alias];
                unset($this->unique_joins[$old_alias]);
            }
        }

        $this->where = str_replace('{' . $old_alias . '}', '{' . $new_alias . '}', $this->where);
    }

    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    public function addJoin($table, $on_condition, $type = DbalQuery::JOIN_LEFT)
    {
        $this->joins[$table] = array(
            'table' => $table,
            'on' => $on_condition,
            'type' => $type
        );
    }

    public function addUniqueJoin($alias, $table, $on_condition, $type = DbalQuery::JOIN_LEFT)
    {
        $this->unique_joins[$alias] = array(
            'table' => $table,
            'on' => $on_condition,
            'type' => $type
        );
    }

    public function setWhereString($where)
    {
        $this->where = (string)$where;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getJoins()
    {
        return $this->joins;
    }

    public function getUniqueJoins()
    {
        return $this->unique_joins;
    }

    public function getWhereString()
    {
        return $this->where;
    }
}
