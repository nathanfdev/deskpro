<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Pagerfanta\Adapter;

use Pagerfanta\Adapter\AdapterInterface;

/**
 * A very simple search adapter that is used only to draw the pager widget.
 * Pass in the $pageinfo (with the key "total_results").
 */
class DeskproSearchAdapter implements AdapterInterface
{
    /**
     * @var array
     */
    private $pageinfo;

    public function __construct(array $pageinfo)
    {
        $this->pageinfo = $pageinfo;
    }

    public function getNbResults()
    {
        return $this->pageinfo['total_results'];
    }

    /**
     * Returns an slice of the results.
     *
     * @param int $offset The offset
     * @param int $length The length
     *
     * @return array|\Traversable The slice
     */
    public function getSlice($offset, $length)
    {
        // not meant to be used
        return [];
    }
}
