<?php

namespace DeskPRO\Component\Pagerfanta;

use DeskPRO\Component\Pagerfanta\Adapter\LimitedAdapter;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;

class LimitedPager extends Pagerfanta
{
    /**
     * {@inheritdoc}
     */
    public function __construct(AdapterInterface $adapter, $limit = 0)
    {
        $limitedAdapter = new LimitedAdapter($adapter, (int) $limit);
        parent::__construct($limitedAdapter);
    }

    /**
     * @return LimitedAdapter
     */
    public function getAdapter()
    {
        return parent::getAdapter();
    }

    /**
     * @return AdapterInterface
     */
    public function getRealAdapter()
    {
        return $this->getAdapter()->getAdapter();
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->getAdapter()->getLimit();
    }

    /**
     * {@inheritdoc}
     */
    public function getNbPages()
    {
        $limit = $this->getLimit();
        $pages = parent::getNbPages();

        if (!$limit) {
            return $pages;
        }

        $maxPage = ceil($this->getNbResults() / $this->getMaxPerPage());

        return min($pages, $maxPage);
    }
}
