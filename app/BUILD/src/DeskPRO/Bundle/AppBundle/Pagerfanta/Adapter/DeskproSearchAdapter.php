<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
     * @param int $offset The offset.
     * @param int $length The length.
     *
     * @return array|\Traversable The slice.
     */
    public function getSlice($offset, $length)
    {
        // not meant to be used
        return [];
    }
}
