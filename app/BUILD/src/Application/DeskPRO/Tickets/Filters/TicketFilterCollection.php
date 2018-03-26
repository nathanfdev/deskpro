<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters;

use Application\DeskPRO\Entity\LegacyTicketFilter;

class TicketFilterCollection
{
    /**
     * @var LegacyTicketFilter[]
     */
    private $filters;

    /**
     * @param LegacyTicketFilter[] $filters
     */
    public function __construct(array $filters)
    {
        $this->filters = array_values($filters);
    }

    /**
     * @return \Application\DeskPRO\Entity\LegacyTicketFilter[]
     */
    public function getAllFilters()
    {
        return $this->filters;
    }

    /**
     * @return \Application\DeskPRO\Entity\LegacyTicketFilter[]
     */
    public function getSystemFilters()
    {
        if (isset($this->cached['getSystemFilters'])) {
            return $this->cached['getSystemFilters'];
        }

        $this->cached['getSystemFilters'] = array_filter($this->filters, function ($f) {
            return $f->sys_name !== null && strpos($f->sys_name, 'w_hold') === false;
        });

        return $this->cached['getSystemFilters'];
    }

    /**
     * @return \Application\DeskPRO\Entity\LegacyTicketFilter[]
     */
    public function getSystemHoldFilters()
    {
        if (isset($this->cached['getSystemHoldFilters'])) {
            return $this->cached['getSystemHoldFilters'];
        }

        $this->cached['getSystemHoldFilters'] = array_filter($this->filters, function ($f) {
            return $f->sys_name !== null && strpos($f->sys_name, 'w_hold') !== false;
        });

        return $this->cached['getSystemHoldFilters'];
    }

    /**
     * @return \Application\DeskPRO\Entity\LegacyTicketFilter[]
     */
    public function getCustomFilters()
    {
        if (isset($this->cached['getCustomFilters'])) {
            return $this->cached['getCustomFilters'];
        }

        $this->cached['getCustomFilters'] = array_filter($this->filters, function ($f) {
            return $f->sys_name === null;
        });

        usort($this->cached['getCustomFilters'], function ($a, $b) {
            $o1 = $a->display_order;
            $o2 = $b->display_order;

            if ($o1 == $o2) {
                return strcmp($a->title, $b->title);
            }

            return $o1 < $o2 ? -1 : 1;
        });

        return $this->cached['getCustomFilters'];
    }
}
