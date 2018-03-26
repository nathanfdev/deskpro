<?php

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
