<?php

/**
 * DeskPRO.
 *
 * @category ORM
 */

namespace Application\DeskPRO\ORM;

class QueryPartial
{
    /** @var string|null */
    protected $order_by = null;
    /** @var string */
    protected $order_dir = 'ASC';
    /** @var int|null */
    protected $first_result = null;
    /** @var int|null */
    protected $max_results = null;

    public function __construct()
    {
    }

    public function setOrderBy($order_by, $order_dir)
    {
        $this->order_by  = $order_by;
        $this->order_dir = $order_dir;

        return $this;
    }

    public function setFirstResult($first_result)
    {
        $this->first_result = $first_result;

        return $this;
    }

    public function setMaxResults($max_results)
    {
        $this->max_results = $max_results;

        return $this;
    }

    public function getOrderBy()
    {
        return [$this->order_by, $this->order_dir];
    }

    public function getFirstResult()
    {
        return $this->first_result;
    }

    public function getMaxResults()
    {
        return $this->max_results;
    }

    public function applyToQuery(\Doctrine\ORM\Query $query)
    {
        if ($this->max_results) {
            $query->setMaxResults($this->max_results);
        }

        if ($this->first_result) {
            $query->setFirstResult($this->first_result);
        }
    }

    public function applyToQueryBuilder(\Doctrine\ORM\QueryBuilder $qb)
    {
        if ($this->max_results) {
            $qb->setMaxResults($this->max_results);
        }

        if ($this->first_result) {
            $qb->setFirstResult($this->first_result);
        }

        if ($this->order_by) {
            $qb->orderBy($this->order_by, $this->order_dir);
        }
    }
}
