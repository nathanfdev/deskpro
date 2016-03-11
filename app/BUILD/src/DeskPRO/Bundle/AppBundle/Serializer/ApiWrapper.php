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

namespace DeskPRO\Bundle\AppBundle\Serializer;

use JMS\Serializer\Annotation as JMS;
use Pagerfanta\Pagerfanta;

/**
 * Class ApiWrapper.
 */
class ApiWrapper
{
    /**
     * @JMS\Exclude()
     *
     * @var array
     */
    protected $includes = [];

    /**
     * @JMS\Groups("wrapper")
     *
     * @var array|Pagerfanta
     */
    protected $data = [];

    /**
     * @JMS\Type("array")
     * @JMS\Groups("wrapper")
     *
     * @var array
     */
    protected $meta = [];

    /**
     * @JMS\Type("array")
     * @JMS\Exclude()
     *
     * It will be filled up just after serialization ends
     *
     * @var array
     */
    protected $linked = [];

    /**
     * ApiWrapper constructor.
     *
     * @param            $data
     * @param array|null $includes
     */
    public function __construct($data, array $includes = [])
    {
        $this->checkPagination($data);
        $this->data     = $data;
        $this->includes = $includes;
    }

    /**
     * @param $data
     */
    protected function checkPagination($data)
    {
        if ($data instanceof Pagerfanta) {
            $total_pages = ceil($data->count() / $data->getMaxPerPage());
            if ($total_pages < 1) {
                $total_pages = 1; // we shouldn't ever report less than 1 total pages
            }

            $pagination = [
                'total'        => $data->count(),
                'count'        => count($data->getCurrentPageResults()),
                'per_page'     => $data->getMaxPerPage(),
                'current_page' => $data->getCurrentPage(),
                'total_pages'  => $total_pages,
            ];

            $results = $data->getCurrentPageResults();
            if($results instanceof \ArrayIterator) {
                $results = $results->getArrayCopy();
            }

            $this->data               = $results;
            $this->meta['pagination'] = $pagination;
        }
    }

    /**
     * @return array
     */
    public function getIncludes()
    {
        return $this->includes;
    }
}
