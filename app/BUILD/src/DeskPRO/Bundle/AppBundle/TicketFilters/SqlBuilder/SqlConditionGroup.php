<?php

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
        if (empty($this->subGroups)) {
            $emptySubGroups = true;
        } else {
            $emptySubGroups = true;
            foreach ($this->subGroups as $g) {
                if (!$g->isEmpty()) {
                    $emptySubGroups = false;
                    break;
                }
            }
        }

        if (empty($this->conds)) {
            $emptyConds = true;
        } else {
            $emptyConds = true;
            foreach ($this->conds as $c) {
                if (!$c->isEmpty()) {
                    $emptyConds = false;
                    break;
                }
            }
        }

        return $emptySubGroups && $emptyConds;
    }
}
