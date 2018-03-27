<?php

namespace DeskPRO\Bundle\AppBundle\Serializer;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class OffsetList.
 */
class OffsetList
{
    /**
     * @var Query|QueryBuilder
     */
    private $query;

    /**
     * @var int
     */
    private $count;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var
     */
    private $total;

    /**
     * @var bool
     */
    private $applyOffset;

    /**
     * Constructor.
     *
     * @param Query|QueryBuilder $query
     * @param int                $count
     * @param int                $offset
     * @param int                $total
     * @param bool               $applyOffset
     */
    public function __construct($query, $count, $offset, $total = null, $applyOffset = true)
    {
        $this->query       = $query;
        $this->count       = $count;
        $this->offset      = $offset;
        $this->total       = $total;
        $this->applyOffset = $applyOffset;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return int|number
     */
    public function getTotal()
    {
        if (null === $this->total) {
            $paginator   = new Paginator($this->query);
            $this->total = $paginator->count();
        }

        return $this->total;
    }

    /**
     * @return array
     */
    public function getData()
    {
        if ($this->query instanceof QueryBuilder) {
            $query = $this->query->getQuery();
        } else {
            $query = $this->query;
        }

        $query->setMaxResults($this->count);
        if ($this->applyOffset) {
            $query->setFirstResult($this->offset);
        }

        return $query->getResult();
    }
}
