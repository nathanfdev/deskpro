<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService\Tickets\LegacyFilterSet;

use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\LegacyTicketFilter;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Searcher\TicketSearch;
use Application\DeskPRO\Tickets\Filters;
use Application\DeskPRO\Tickets\GroupingCounter;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\Model\TicketGrouping;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class LegacyTicketFilterSetDataService.
 */
class LegacyTicketFilterSetDataService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(EntityManager $em, TokenStorageInterface $tokenStorage)
    {
        $this->em           = $em;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array
     */
    public function getTermMapping()
    {
        $termMapping = [
            TicketGrouping::DEPARTMENT       => TicketSearch::TERM_DEPARTMENT,
            TicketGrouping::ORGANIZATION     => TicketSearch::TERM_ORGANIZATION,
            TicketGrouping::PERSON           => TicketSearch::TERM_PERSON,
            TicketGrouping::LANGUAGE         => TicketSearch::TERM_LANGUAGE,
            TicketGrouping::URGENCY          => TicketSearch::TERM_URGENCY,
            TicketGrouping::AGENT            => TicketSearch::TERM_AGENT,
            TicketGrouping::AGENT_TEAM       => TicketSearch::TERM_AGENT_TEAM,
            TicketGrouping::WAITING_TIME     => TicketSearch::TERM_USER_WAITING,
            TicketGrouping::ALL_WAITING_TIME => TicketSearch::TERM_TOTAL_USER_WAITING,
            TicketGrouping::DATE_CREATED     => TicketSearch::TERM_DATE_CREATED,
        ];

        /** @var \Application\DeskPRO\EntityRepository\CustomDefTicket $customDefRepo */
        $customDefRepo = $this->em->getRepository(CustomDefTicket::class);
        foreach ($customDefRepo->getEnabledTopFields() as $customDef) {
            foreach (['.', '_'] as $delimiter) {
                $customDefId = $customDef->getId();
                $queryParam  = TicketGrouping::CUSTOM_FIELD_COLUMN_PREFIX.$delimiter.$customDefId;
                $customTerm  = TicketSearch::TERM_TICKET_FIELD.'_'.$customDefId;

                $termMapping[$queryParam] = $customTerm;
            }
        }

        return $termMapping;
    }

    /**
     * @return \Application\DeskPRO\Entity\LegacyTicketFilter[]
     */
    public function getAllFilters()
    {
        $filtersService = new Filters();

        $filters = $filtersService->getFiltersForPerson($this->getUser());
        $filters = array_filter($filters, function (LegacyTicketFilter $filter) {
            return !$filter->isProblemFilter();
        });

        uasort($filters, function (LegacyTicketFilter $a, LegacyTicketFilter $b) {
            return $a->getDisplayOrder() - $b->getDisplayOrder();
        });

        return $filters;
    }

    /**
     * @param int $id
     *
     * @return LegacyTicketFilterSet
     */
    public function getFilterSet($id)
    {
        $sets = $this->getFilterSetsWithData();

        return isset($sets[$id]) ? $sets[$id] : null;
    }

    /**
     * @return LegacyTicketFilterSet[]
     */
    public function getAllFilterSets()
    {
        return array_values($this->getFilterSetsWithData());
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        $repository = $this->em->getRepository(LegacyTicketFilter::class);

        $count   = 0;
        $filters = [
            $repository->findOneBy(['sys_name' => 'all']),
            $repository->findOneBy(['sys_name' => 'all_w_hold']),
        ];

        foreach ($filters as $filter) {
            if (!$filter) {
                continue;
            }

            $count += $this->getFilterCount($filter)->getCount();
        }

        return $count;
    }

    /**
     * @param LegacyTicketFilterSet $set
     * @param array|null            $groupBy
     *
     * @return Count
     */
    public function getFilterSetCount(LegacyTicketFilterSet $set, $groupBy = null)
    {
        return $this->getFiltersCount($set->getId(), 'ticket_filter_set', $set->getTitle(), $set->getFilters(), $groupBy);
    }

    /**
     * @param int                  $id
     * @param string               $type
     * @param string               $title
     * @param LegacyTicketFilter[] $filters
     * @param array|null           $groupBy
     *
     * @return Count
     */
    public function getFiltersCount($id, $type, $title, array $filters, $groupBy = null)
    {
        $count   = Count::create(0, $id, $type, $title);
        $groupBy = $groupBy ?: [];

        foreach ($filters as $filter) {
            $filterGroupBy = !empty($groupBy[$filter->getId()]) ? $groupBy[$filter->getId()] : null;
            $count->addNestedInstance($this->getFilterCount($filter,  $filterGroupBy), true);
        }

        return $count;
    }

    /**
     * @param LegacyTicketFilter $filter
     * @param string|null        $groupBy
     *
     * @return Count
     */
    public function getFilterCount(LegacyTicketFilter $filter, $groupBy = null)
    {
        $searcher      = $this->getFilterSearcher($filter);
        $termMapping   = $this->getTermMapping();
        $legacyGroupBy = isset($termMapping[$groupBy]) ? $termMapping[$groupBy] : null;

        if ($legacyGroupBy) {
            $ticketIds = $searcher->getMatches();

            $grouper = new GroupingCounter();
            $grouper->setGrouping($legacyGroupBy);
            $grouper->setMode('specify', $ticketIds);

            $groupedInfo = $grouper->getDisplayArray();
            $totalInfo   = array_shift($groupedInfo['items']);

            $count = Count::create($totalInfo['total'], $filter->getId(), 'filter', $filter->getRawTitle(), $groupBy);
            foreach ($groupedInfo['items'] as $nestedItem) {
                $count->addNested($nestedItem['total'], $nestedItem['id'], $groupBy, isset($nestedItem['title']) ? $nestedItem['title'] : null);
            }
        } else {
            $count = Count::create($searcher->getCount(), $filter->getId(), 'filter', $filter->getRawTitle(), 'filter');
        }

        return $count;
    }

    /**
     * @param LegacyTicketFilter $filter
     *
     * @return int
     */
    public function getFilterSetType(LegacyTicketFilter $filter)
    {
        if (!$filter->sys_name) {
            return LegacyTicketFilterSet::TYPE_CUSTOM_FILTERS;
        } elseif (strpos($filter->sys_name, 'archive_') === 0) {
            return LegacyTicketFilterSet::TYPE_ALL_TICKETS;
        }

        return LegacyTicketFilterSet::TYPE_AWAITING_AGENT;
    }

    /**
     * @param LegacyTicketFilter $filter
     *
     * @return \Application\DeskPRO\Searcher\TicketSearch
     */
    public function getFilterSearcher(LegacyTicketFilter $filter)
    {
        $searcher = $filter->getSearcher();
        $searcher->setPersonContext($this->getUser());

        return $searcher;
    }

    /**
     * @return LegacyTicketFilterSet[]
     */
    private function getFilterSets()
    {
        return [
            LegacyTicketFilterSet::TYPE_AWAITING_AGENT => new LegacyTicketFilterSet(LegacyTicketFilterSet::TYPE_AWAITING_AGENT, 'Awaiting agent', 1),
            LegacyTicketFilterSet::TYPE_ALL_TICKETS    => new LegacyTicketFilterSet(LegacyTicketFilterSet::TYPE_ALL_TICKETS, 'All tickets', 2),
            LegacyTicketFilterSet::TYPE_CUSTOM_FILTERS => new LegacyTicketFilterSet(LegacyTicketFilterSet::TYPE_CUSTOM_FILTERS, 'Custom filters', 3),
        ];
    }

    /**
     * @return array
     */
    private function getFilterSetsWithData()
    {
        $filter_sets = $this->getFilterSets();
        foreach ($this->getAllFilters() as $filter) {
            $filter_sets[$this->getFilterSetType($filter)]->addFilter($filter);
        }

        return $filter_sets;
    }

    /**
     * @return Person
     */
    private function getUser()
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $user->loadHelper('AgentTeam');
        $user->loadHelper('AgentPermissions');

        return $user;
    }
}
