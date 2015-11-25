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
namespace DeskPRO\Bundle\AppBundle\DataService\Tickets;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\DbalTicketFilterEngine;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class TicketCountsDataService.
 */
class TicketCountsDataService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var DbalTicketFilterEngine
     */
    private $engine;

    /**
     * @var Person
     */
    private $user;

    /**
     * @param EntityManager          $em
     * @param DbalTicketFilterEngine $engine
     * @param TokenStorage           $tokenStorage
     */
    public function __construct(EntityManager $em, DbalTicketFilterEngine $engine, TokenStorage $tokenStorage)
    {
        $this->em     = $em;
        $this->engine = $engine;
        $this->user   = $tokenStorage->getToken()->getUser();
    }

    /**
     * @param TicketFilter $filter
     * @param string       $group_by
     *
     * @return Count
     */
    public function getTicketFilterCount(TicketFilter $filter, $group_by = null)
    {
        $context = new TermEngineContext($this->user);
        if ($group_by) {
            $context->addGroupByFromString($group_by);
        }

        /** @var \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalExecutableQuery $tickets_query */
        $tickets_query = $this->engine->evaluate($filter, $context);

        if ($group_by) {
            $filter_counts = $tickets_query->fetchGroupedCount();
            $filter_count  = Count::create(0, $filter->getId(), [], 'filter');
            foreach ($filter_counts as $nested_count) {
                $value = $nested_count['count'];
                unset($nested_count['count']);
                $group = array_pop($nested_count);
                $group = ctype_digit($group) ? (int) $group : $group;

                $filter_count->addNestedInstance(
                    Count::create($value, $group, [], $group_by),
                    true
                );
            }

            return $filter_count;
        } else {
            return Count::create($tickets_query->fetchCount(), $filter->getId(), [], 'filter');
        }
    }

    /**
     * @param TicketFilterSet $set
     * @param array|null      $group_by
     *
     * @return Count
     */
    public function getFilterSetTicketsCount(TicketFilterSet $set, array $group_by = null)
    {
        is_array($group_by) or $group_by = [];
        $total                           = 0;
        $counts                          = [];

        foreach ($set->getFilters() as $filter) {
            $filter_grouping = array_key_exists($filter->getId(), $group_by) ? $group_by[$filter->getId()] : null;
            $filter_count    = $this->getTicketFilterCount($filter, $filter_grouping);
            $counts[]        = $filter_count;
            $total += $filter_count->getCount();
        }

        return Count::create($total, $set->getId(), $counts);
    }
}
