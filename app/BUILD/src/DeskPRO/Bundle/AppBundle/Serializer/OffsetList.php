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
