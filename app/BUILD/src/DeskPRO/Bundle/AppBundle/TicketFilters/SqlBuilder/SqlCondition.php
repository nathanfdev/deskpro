<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder;

/**
 * A QueryCondition represents a self-contained part of a query for use in a filter.
 * Variables and table aliases have a "local name" that are used within conditional
 * clauses (e.g. WHERE or ON) which are re-named when combined back into the main query.
 *
 * Notes:
 * - Variables must be used as named params. Positional params aren't supported.
 * - The syntax for referring to tables (e.g. joins) is special in this component.
 *   Join aliases must be surrounded by curlies as demonstrated below. The special
 *   alias {from} refers to the base table (the implementation may also define a
 *   default alias, e.g. {ticket} and {from} could be the same thing).
 *
 * <code>
 * $q->setParam('foo', 123);
 * $a->addUniqueJoin('ticket', 'ticket_participants', 'part', '{part}.ticket_id = {from}.id');
 * $q->setWhere('{part}.agent_id = :foo');
 * </code>
 *
 * When the QueryCondition is compiled into a real query, the param name and unique joins are namespaced so other
 * parts using the same params don't conflict.
 */
class SqlCondition
{
    const JOIN_LEFT  = 'LEFT';
    const JOIN_RIGHT = 'RIGHT';
    const JOIN_INNER = 'INNER';

    /**
     * Array of localName => value.
     *
     * @var array
     */
    private $params = [];

    /**
     * @var string
     */
    private $where = '';

    /**
     * @var array
     */
    private $sharedJoins = [];

    /**
     * @var array
     */
    private $uniqueJoins = [];

    /**
     * @param string $name
     * @param string $value
     * @param string $paramType
     *
     * @return $this
     */
    public function setParam($name, $value, $paramType = null)
    {
        $this->params[$name] = [
            $value,
            $paramType,
        ];

        return $this;
    }

    /**
     * Add a join on a common table that will be shared between multiple terms.
     *
     * @param string $fromAlias The table alias that the join is on. Typically this would be {from} for the "main" table
     * @param string $table     The name of the table
     * @param string $alias     The alias to use when referencing the table within this query part
     * @param string $on        The ON condition
     * @param string $type      The type of join
     *
     * @return $this
     */
    public function addSharedJoin($fromAlias, $table, $alias, $on, $type = self::JOIN_LEFT)
    {
        $type = strtoupper($type);
        if (
            $type !== self::JOIN_LEFT
            && $type !== self::JOIN_RIGHT
            && $type !== self::JOIN_INNER
        ) {
            throw new \InvalidArgumentException('Invalid join type');
        }

        $this->sharedJoins[$table] = [
            'fromAlias'  => $fromAlias,
            'table'      => $table,
            'localAlias' => $alias,
            'on'         => $on,
            'type'       => $type,
        ];

        return $this;
    }

    /**
     * Add a join that is meant to be used only from within this term. It means the join
     * is unique and may result in multiple joins on the same table if different terms are used.
     *
     * @param string $fromAlias The name of the table. Typically this would be {from} for the "main" table
     * @param string $table     The name of the table
     * @param string $alias     The alias to use when referencing the table within this query part
     * @param string $on        The ON condition
     * @param string $type      The type of join
     *
     * @return $this
     */
    public function addUniqueJoin($fromAlias, $table, $alias, $on, $type = self::JOIN_LEFT)
    {
        $type = strtoupper($type);
        if (
            $type !== self::JOIN_LEFT
            && $type !== self::JOIN_RIGHT
            && $type !== self::JOIN_INNER
        ) {
            throw new \InvalidArgumentException('Invalid join type');
        }

        $this->uniqueJoins[$table] = [
            'fromAlias'  => $fromAlias,
            'table'      => $table,
            'localAlias' => $alias,
            'on'         => $on,
            'type'       => $type,
        ];

        return $this;
    }

    /**
     * Set the WHERE string. If an array is supplied, then the where string will be combined with ANDs.
     *
     * @param string $where
     *
     * @return $this
     */
    public function setWhere($where)
    {
        if (is_array($where)) {
            $where = implode(' AND ', $where);
        }

        $this->where = $where;

        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @return array
     */
    public function getSharedJoins()
    {
        return $this->sharedJoins;
    }

    /**
     * @return array
     */
    public function getUniqueJoins()
    {
        return $this->uniqueJoins;
    }
}
