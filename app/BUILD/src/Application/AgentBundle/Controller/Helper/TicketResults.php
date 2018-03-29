<?php

namespace Application\AgentBundle\Controller\Helper;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\LegacyTicketFilter;
use Application\DeskPRO\Entity\ResultCache;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Searcher\TicketSearch;
use Application\DeskPRO\Tickets\GroupingCounter;
use Orb\Util\Arrays;

/**
 * Handles ticket searches.
 */
class TicketResults
{
    /**
     * @var \Application\AgentBundle\Controller\AbstractController
     */
    protected $controller;

    /**
     * @var array
     */
    protected $ticket_ids = [];

    /**
     * @var array
     */
    protected $grouped_ticket_ids = null;

    /**
     * @var string
     */
    protected $group_field = null;

    /**
     * @var string
     */
    protected $order_by = null;

    /**
     * @var array
     */
    protected $group_display_info = null;

    /**
     * @var string
     */
    protected $grouping_summary;

    /**
     * @param $controller
     * @param LegacyTicketFilter $filter
     *
     * @return TicketResults
     */
    public static function newFromFilter($controller, LegacyTicketFilter $filter)
    {
        $helper = new self($controller);
        $helper->setTicketIds($filter->getResults($controller->getPerson()));

        if ($controller->in->getString('group_by')) {
            $helper->setGroupField($controller->in->getString('group_by'));
        } elseif ($filter['group_by']) {
            $helper->setGroupField($filter['group_by']);
        }

        // Or if the user has their own
        $group_by = $controller->getPerson()->getPref('agent.ui.ticket-filter-group-by.'.$filter['id']);
        if ($group_by) {
            $helper->setGroupField($group_by);
        }

        $helper->setGroupOrderBy($filter->getSearcher()->getOrderBy());

        return $helper;
    }

    /**
     * @param $controller
     * @param ResultCache $result_cache
     *
     * @return TicketResults
     */
    public static function newFromResultCache($controller, ResultCache $result_cache)
    {
        $helper = new self($controller);
        $helper->setTicketIds($result_cache['results']);

        if (!empty($result_cache['criteria']['order_by'])) {
            $helper->setGroupOrderBy($result_cache['criteria']['order_by']);
        }
        if (!empty($result_cache['criteria']['group_by'])) {
            $helper->setGroupField($result_cache['criteria']['group_by']);
        }

        $extra = $result_cache['extra'];
        if (!empty($extra['group_by'])) {
            $helper->setGroupField($extra['group_by']);
        }

        return $helper;
    }

    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Set ticket IDs for the search results.
     *
     * @param array $ticket_ids
     */
    public function setTicketIds(array $ticket_ids)
    {
        $this->ticket_ids = $ticket_ids;
    }

    /**
     * Get ticket IDs.
     *
     * @return array
     */
    public function getTicketIds()
    {
        return $this->ticket_ids;
    }

    /**
     * Get total number of matches.
     *
     * @return int
     */
    public function getCount()
    {
        return count($this->getTicketIds());
    }

    /**
     * Get ticket IDs that match the current group.
     *
     * @param $field_id
     *
     * @return array
     */
    public function getGroupTicketIds($field_id)
    {
        if ($this->grouped_ticket_ids !== null) {
            return $this->grouped_ticket_ids;
        }
        if ($this->group_field === null) {
            return [];
        }

        $searcher = new TicketSearch();
        $searcher->setPersonContext($this->controller->getPerson());
        $searcher->addTerm(TicketSearch::TERM_ID, TicketSearch::OP_IS, $this->getTicketIds());

        $term = GroupingCounter::getSearchTerm($this->group_field, $field_id);

        $searcher->addTerm($term['type'], $term['op'], $term['options']);

        if ($this->order_by) {
            $searcher->setOrderByCode($this->order_by);
        }

        $this->grouped_ticket_ids = $searcher->getMatches();
        $this->grouped_ticket_ids = Arrays::castToType($this->grouped_ticket_ids, 'int');

        return $this->grouped_ticket_ids;
    }

    /**
     * @param $page
     * @param int $per_page
     *
     * @return array
     */
    public function getTicketsForPage($page, $per_page = 50)
    {
        return $this->_getPageFromTicketIds($this->getTicketIds(), $page, $per_page);
    }

    /**
     * @param $cursor_start
     * @param int $per_page
     *
     * @return mixed
     */
    public function getTicketsForCursorPage($cursor_start, $per_page = 50)
    {
        return $this->_getCursorPageFromTicketIds($this->getTicketIds(), $cursor_start, $per_page);
    }

    /**
     * @param $field_id
     * @param $page
     * @param int $per_page
     *
     * @return array
     */
    public function getGroupedTicketsForPage($field_id, $page, $per_page = 50)
    {
        return $this->_getPageFromTicketIds($this->getGroupTicketIds($field_id), $page, $per_page);
    }

    /**
     * @param $field_id
     * @param $page
     * @param int $per_page
     *
     * @return array
     */
    public function getGroupedTicketsForCursorPage($field_id, $page, $per_page = 50)
    {
        return $this->_getCursorPageFromTicketIds($this->getGroupTicketIds($field_id), $page, $per_page);
    }

    /**
     * @param array $ticket_ids
     * @param $page
     * @param $per_page
     *
     * @return array
     */
    protected function _getPageFromTicketIds(array $ticket_ids, $page, $per_page)
    {
        $page_ticket_ids = Arrays::getPageChunk($ticket_ids, $page, $per_page);
        $tickets_raw     = App::getEntityRepository(Ticket::class)->getTicketsResultsFromIds($page_ticket_ids);

        // - We'll get a page of results, but that actual page isn't going to be
        // sorted the way we want, because MySQL was just sent a list of ID's.
        // - So we'll re-create the array here according to the order they're supposed to be in.
        $tickets = [];
        foreach ($ticket_ids as $tid) {
            if (isset($tickets_raw[$tid])) {
                $tickets[$tid] = $tickets_raw[$tid];
            }
        }

        return $tickets;
    }

    /**
     * @param array $ticket_ids
     * @param $cursor_start
     * @param $per_page
     *
     * @return array
     */
    protected function _getCursorPageFromTicketIds(array $ticket_ids, $cursor_start, $per_page)
    {
        $page_ticket_ids = array_slice($ticket_ids, $cursor_start, $per_page);
        $tickets_raw     = App::getEntityRepository(Ticket::class)->getTicketsResultsFromIds($page_ticket_ids);

        // - We'll get a page of results, but that actual page isn't going to be
        // sorted the way we want, because MySQL was just sent a list of ID's.
        // - So we'll re-create the array here according to the order they're supposed to be in.
        $tickets = [];
        foreach ($ticket_ids as $tid) {
            if (isset($tickets_raw[$tid])) {
                $tickets[$tid] = $tickets_raw[$tid];
            }
        }

        return $tickets;
    }

    /**
     * Set the grouping field.
     *
     * @param string $field
     */
    public function setGroupField($field)
    {
        $this->group_field = $field;
    }

    /**
     * Get the grouping field.
     *
     * @return null|string
     */
    public function getGroupField()
    {
        return $this->group_field;
    }

    /**
     * Set the order by that will be used for sub-grouping. Tickets area
     * already sorted, so this is only used for fetching grouped results.
     *
     * @param array $order_by
     */
    public function setGroupOrderBy($order_by)
    {
        $this->order_by = $order_by;
    }

    /**
     * Get counts and titles for the grouping options.
     *
     * @return array
     */
    public function getGroupDisplayInfo()
    {
        if ($this->group_display_info !== null) {
            return $this->group_display_info;
        }
        if ($this->group_field === null) {
            return;
        }

        $grouper = new GroupingCounter();
        $grouper->setGrouping($this->group_field);
        $grouper->setMode('specify', $this->getTicketIds());

        $this->group_display_info = $grouper->getDisplayArray();
        //$grouper->sortDisplayArray($this->group_display_info);

        $this->grouping_summary = $grouper->getGroupingSummary();

        return $this->group_display_info;
    }

    /**
     * Get the grouping field phrase.
     *
     * @return string
     */
    public function getGroupingSummary()
    {
        if ($this->grouping_summary) {
            return $this->grouping_summary;
        }

        return '';
    }

    /**
     * Do we have enough info to run grouping? aka if we havea group_field set.
     *
     * @return bool
     */
    public function isGroupable()
    {
        if ($this->group_field) {
            return true;
        }

        return false;
    }
}
