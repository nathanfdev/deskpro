<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\DataService\Tickets\LegacyFilterSet;

use Application\DeskPRO\Entity\LegacyTicketFilter;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Tickets\Filters;
use Application\DeskPRO\Tickets\GroupingCounter;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
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
    private $token_storage;

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param TokenStorageInterface $token_storage
     */
    public function __construct(EntityManager $em, TokenStorageInterface $token_storage)
    {
        $this->em            = $em;
        $this->token_storage = $token_storage;
    }

    /**
     * @return \Application\DeskPRO\Entity\LegacyTicketFilter[]
     */
    public function getAllFilters()
    {
        $filters = new Filters();

        return $filters->getFiltersForPerson($this->getUser());
    }

    /**
     * @param int $id
     *
     * @return LegacyTicketFilterSet
     */
    public function getFilterSet($id)
    {
        $sets = $this->getFilterSetsData();

        return isset($sets[$id]) ? $sets[$id] : null;
    }

    /**
     * @return LegacyTicketFilterSet[]
     */
    public function getAllFilterSets()
    {
        return array_values($this->getFilterSetsData());
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
     * @param LegacyTicketFilter $filter
     * @param string|null        $group_by
     *
     * @return Count
     */
    public function getFilterCount(LegacyTicketFilter $filter, $group_by = null)
    {
        $searcher = $filter->getSearcher();
        $searcher->setPersonContext($this->getUser());

        if ($group_by) {
            $ticket_ids = $searcher->getMatches();

            $grouper = new GroupingCounter();
            $grouper->setGrouping($group_by);
            $grouper->setMode('specify', $ticket_ids);

            $grouped_info = $grouper->getDisplayArray();
            $total_info   = array_shift($grouped_info['items']);

            $count = Count::create($total_info['total'], $filter->getId(), 'filter', $filter->getRawTitle(), $group_by);
            foreach ($grouped_info['items'] as $nested_item) {
                $count->addNested($nested_item['total'], $nested_item['id'], $group_by, isset($nested_item['title']) ? $nested_item['title'] : null);
            }
        } else {
            $count = Count::create($searcher->getCount(), $filter->getId(), 'filter', $filter->getRawTitle(), 'filter');
        }

        return $count;
    }

    /**
     * @return array
     */
    private function getFilterSetsData()
    {
        $awaiting_agent_set = new LegacyTicketFilterSet(LegacyTicketFilterSet::TYPE_AWAITING_AGENT, 'Awaiting agent', 1);
        $all_tickets_set    = new LegacyTicketFilterSet(LegacyTicketFilterSet::TYPE_ALL_TICKETS, 'All tickets', 2);
        $custom_filters_set = new LegacyTicketFilterSet(LegacyTicketFilterSet::TYPE_CUSTOM_FILTERS, 'Custom filters', 3);

        foreach ($this->getAllFilters() as $filter) {
            if (!$filter->sys_name) {
                $custom_filters_set->addFilter($filter);
            } elseif (strpos($filter->sys_name, 'archive_') === 0) {
                $all_tickets_set->addFilter($filter);
            } else {
                $awaiting_agent_set->addFilter($filter);
            }
        }

        return [
            LegacyTicketFilterSet::TYPE_AWAITING_AGENT => $awaiting_agent_set,
            LegacyTicketFilterSet::TYPE_ALL_TICKETS    => $all_tickets_set,
            LegacyTicketFilterSet::TYPE_CUSTOM_FILTERS => $custom_filters_set,
        ];
    }

    /**
     * @return Person
     */
    private function getUser()
    {
        $user = $this->token_storage->getToken()->getUser();
        $user->loadHelper('AgentTeam');
        $user->loadHelper('AgentPermissions');

        return $user;
    }
}
