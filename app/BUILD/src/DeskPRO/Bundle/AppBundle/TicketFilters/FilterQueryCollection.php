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

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Filter;
use DeskPRO\Component\FilterQueryLanguage\QueryIterator;
use DeskPRO\Component\Util\ListUtils;

class FilterQueryCollection
{
    /**
     * @var Filter[]
     */
    private $filters;

    /**
     * fieldId => Filter[].
     *
     * @var Filter[]
     */
    private $fieldToFilters;

    /**
     * FilterQueryCollection constructor.
     *
     * @param Filter[] $filters
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * @param array $fields
     *
     * @return Filter[]
     */
    public function findAffectedFilters(array $fields)
    {
        if ($this->fieldToFilters === null) {
            $this->buildFieldMap();
        }

        $filters = [];

        foreach ($fields as $fid) {
            if (!empty($this->fieldToFilters[$fid])) {
                $filters = array_merge($filters, $this->fieldToFilters[$fid]);
            }
        }

        return ListUtils::unique($filters);
    }

    private function buildFieldMap()
    {
        $this->fieldToFilters = [];

        foreach ($this->filters as $filter) {
            $fields = QueryIterator::collectFieldIds($filter->query);
            foreach ($fields as $fid) {
                if (!isset($this->fieldToFilters[$fid])) {
                    $this->fieldToFilters[$fid] = [];
                }

                $this->fieldToFilters[$fid][] = $filter;
            }
        }
    }
}
