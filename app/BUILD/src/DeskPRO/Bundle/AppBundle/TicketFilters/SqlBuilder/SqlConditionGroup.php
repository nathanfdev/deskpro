<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

class SqlConditionGroup
{
    const OP_AND = 'AND';
    const OP_OR  = 'OR';
    const OP_NOT = 'NOT';

    /**
     * @var string
     */
    private $op;

    /**
     * @var SqlCondition[]
     */
    private $conds = [];

    /**
     * @var SqlConditionGroup[]
     */
    private $subGroups = [];

    /**
     * QueryConditionGroup constructor.
     *
     * @param string $op
     */
    public function __construct($op)
    {
        $this->op = $op;
    }

    /**
     * @return SqlConditionGroup
     */
    public static function createAndGroup()
    {
        return new self(self::OP_AND);
    }

    /**
     * @return SqlConditionGroup
     */
    public static function createOrGroup()
    {
        return new self(self::OP_OR);
    }

    /**
     * @return SqlConditionGroup
     */
    public static function createNotGroup()
    {
        return new self(self::OP_NOT);
    }

    /**
     * @param $part
     *
     * @return $this
     */
    public function add($part)
    {
        switch (true) {
            case $part instanceof self:
                $this->addSubGroup($part);
                break;

            case $part instanceof SqlCondition:
                $this->addCondition($part);
                break;

            default:
                throw new \InvalidArgumentException();
        }

        return $this;
    }

    /**
     * @param SqlConditionGroup $g
     *
     * @return $this
     */
    public function addSubGroup(SqlConditionGroup $g)
    {
        $this->subGroups[] = $g;

        return $this;
    }

    /**
     * @param SqlCondition $c
     *
     * @return $this
     */
    public function addCondition(SqlCondition $c)
    {
        $this->conds[] = $c;

        return $this;
    }

    /**
     * @return SqlConditionGroup[]
     */
    public function getSubGroups()
    {
        return $this->subGroups;
    }

    /**
     * @return array
     */
    public function getConditions()
    {
        return $this->conds;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->op;
    }

    /**
     * @return int
     */
    public function countSubGroups()
    {
        return count($this->subGroups);
    }

    /**
     * @return int
     */
    public function countConditions()
    {
        return count($this->conds);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->subGroups) && empty($this->conds);
    }
}
